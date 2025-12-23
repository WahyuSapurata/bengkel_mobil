<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProdukRequest;
use App\Http\Requests\UpdateProdukRequest;
use App\Models\DetailPembelian;
use App\Models\Kategori;
use App\Models\Opname;
use App\Models\PriceHistory;
use App\Models\Produk;
use App\Models\StokHistory;
use App\Models\Suplayer;
use App\Models\WirehouseStock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProdukController extends BaseController
{
    public function index()
    {
        $module = 'Produk';
        // Ambil data kategori dan suplayer untuk dropdown
        $kategoris = Kategori::select('uuid', 'nama_kategori', 'sub_kategori')->get();
        $suplayers = Suplayer::select('uuid', 'nama')->get();

        return view('pages.produk.index', compact('module', 'kategoris', 'suplayers'));
    }

    public function getSubKategori($uuid)
    {
        $kategori = Kategori::where('uuid', $uuid)->firstOrFail();
        $sub = json_decode($kategori->sub_kategori, true);

        return response()->json($sub);
    }

    public function price_history($params)
    {
        $produk = Produk::where('uuid', $params)->first();
        $module = 'Price History ' . $produk->nama_barang;
        return view('pages.produk.price_history', compact('module', 'produk'));
    }

    public function opname_stock($uuid_produk)
    {
        $user = Auth::user();
        $produk = Produk::where('uuid', $uuid_produk)->firstOrFail();

        $stock = DB::table('stok_histories as sh')
            ->where('sh.uuid_produk', $produk->uuid)
            ->sum('sh.stock');

        // Gunakan hasil opname terakhir jika ada, kalau tidak gunakan stok wirehouse
        $total_stok = $stock;

        $module = 'Opname Stock Gudang Pusat - ' . $produk->nama_barang . ' (' . $total_stok . ')';

        return view('pages.produk.opname_stock', compact('module', 'produk', 'total_stok'));
    }

    public function get_price_history(Request $request, $params)
    {
        $produk = Produk::where('uuid', $params)->first();
        $columns = [
            'price_histories.uuid',
            'price_histories.uuid_produk',
            'price_histories.harga',
            'price_histories.created_at',
            'produks.nama_barang as nama_barang',
        ];

        // Hitung total data tanpa filter
        $totalData = PriceHistory::where('uuid_produk', $produk->uuid)->count();

        // Query utama dengan join ke tabel produk
        $query = PriceHistory::select($columns)
            ->join('produks', 'produks.uuid', '=', 'price_histories.uuid_produk')
            ->where('price_histories.uuid_produk', $produk->uuid);

        // Searching
        if (!empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search, $columns) {
                foreach ($columns as $column) {
                    // Hilangkan alias saat searching
                    $colName = explode(' as ', $column)[0];
                    $q->orWhere($colName, 'like', "%{$search}%");
                }
            });
        }

        // Hitung total data setelah filter
        $totalFiltered = $query->count();

        // Sorting
        if (!empty($request->order)) {
            $orderCol = explode(' as ', $columns[$request->order[0]['column']])[0];
            $orderDir = $request->order[0]['dir'];
            $query->orderBy($orderCol, $orderDir);
        } else {
            $query->latest('created_at');
        }

        // Pagination
        $query->skip($request->start)->take($request->length);

        // Ambil data
        $data = $query->get();

        // Response JSON untuk DataTables
        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ]);
    }

    public function get(Request $request)
    {
        $user = Auth::user();

        $columns = [
            'produks.uuid',
            'produks.uuid_kategori',
            'produks.uuid_suplayer',
            'produks.sub_kategori',
            'produks.kode',
            'produks.nama_barang',
            'produks.merek',
            'produks.hrg_modal',
            'produks.profit',
            'produks.minstock',
            'produks.maxstock',
            'produks.satuan',
            'produks.foto',
            'produks.created_at',
            'produks.created_by',
            'produks.update_by',
            'kategoris.nama_kategori as kategori',
            'suplayers.nama as suplayer',
        ];

        $totalData = Produk::count();

        $query = Produk::select(array_merge($columns, [
            DB::raw("(SELECT COALESCE(SUM(dp.qty),0)
            FROM detail_pembelians dp
            JOIN pembelians pb ON pb.uuid = dp.uuid_pembelian
            WHERE dp.uuid_produk = produks.uuid) as total_pembelian"),

            DB::raw("(SELECT COALESCE(SUM(o.stock),0)
            FROM opnames o
            WHERE o.uuid_user = '" . $user->uuid . "'
            AND o.uuid_produk = produks.uuid) as total_opname"),

            // ==== Total stok dengan logika opname + filter warehouse ====
            DB::raw("
                (SELECT COALESCE(SUM(sh.stock),0)
            FROM stok_histories sh
            WHERE sh.uuid_produk = produks.uuid) AS total_stok
                "),

            DB::raw('
                ROUND(
                    (
                        CAST(produks.hrg_modal AS DECIMAL(15,2))
                        + (CAST(produks.hrg_modal AS DECIMAL(15,2)) * CAST(produks.profit AS DECIMAL(15,2)) / 100)
                    ) / 1000
                ) * 1000 as harga_jual
            ')
        ]))
            ->leftJoin('kategoris', 'kategoris.uuid', '=', 'produks.uuid_kategori')
            ->leftJoin('suplayers', 'suplayers.uuid', '=', 'produks.uuid_suplayer');

        // ==== Filter kategori dan supplier
        if ($request->filled('uuid_kategori')) {
            $query->where('produks.uuid_kategori', $request->uuid_kategori);
        }
        if ($request->filled('uuid_suplayer')) {
            $query->where('produks.uuid_suplayer', $request->uuid_suplayer);
        }

        // ==== Filter warehouse (kalau bukan pusat)
        if (!$user->is_pusat && $request->filled('uuid')) {
            $query->whereExists(function ($sub) use ($request) {
                $sub->select(DB::raw(1))
                    ->from('wirehouse_stocks as ws')
                    ->whereColumn('ws.uuid_produk', 'produks.uuid');
            });
        }

        // ==== Searching
        if (!empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search, $columns) {
                foreach ($columns as $column) {
                    $colName = explode(' as ', $column)[0];
                    $q->orWhere($colName, 'like', "%{$search}%");
                }
            });
        }

        $totalFiltered = $query->count();

        // ==== Sorting
        if (!empty($request->order)) {
            $columnIndex = $request->order[0]['column'];
            $orderDir = $request->order[0]['dir'] ?? 'asc';
            $orderCol = explode(' as ', $columns[$columnIndex] ?? 'produks.created_at')[0];
            $query->orderBy($orderCol, $orderDir)->orderBy('produks.created_at', 'desc');
        } else {
            $query->orderBy('produks.created_at', 'desc');
        }

        // ==== Pagination
        $query->skip($request->start)->take($request->length);

        $data = $query->get();

        // Hitung total harga modal
        $totalHargaModal = Produk::sum('hrg_modal');

        // Hitung total harga jual (pakai formula dari select harga_jual)
        $totalHargaJual = Produk::select(DB::raw('
    SUM(
        ROUND(
            (
                CAST(hrg_modal AS DECIMAL(15,2))
                + (CAST(hrg_modal AS DECIMAL(15,2)) * CAST(profit AS DECIMAL(15,2)) / 100)
            ) / 1000
        ) * 1000
    ) as total_harga_jual
'))->value('total_harga_jual');

        // Subquery untuk stok per produk
        $sub = Produk::select(
            'produks.uuid',
            'produks.hrg_modal',
            'produks.profit',
            DB::raw("
(SELECT COALESCE(SUM(sh.stock),0)
            FROM stok_histories sh
            WHERE sh.uuid_produk = produks.uuid) AS total_stok
")
        );

        // Bungkus subquery supaya bisa dihitung SUM-nya
        $wrapped = DB::table(DB::raw("({$sub->toSql()}) as x"))
            ->mergeBindings($sub->getQuery());

        // Total stock
        $totalStock = $wrapped->sum('total_stok');

        // Total harga modal × stok
        $totalHargaModalKaliStock = $wrapped->selectRaw('SUM(hrg_modal * total_stok) as total')->value('total');

        // Total harga jual × stok (ikutin formula jual)
        $totalHargaJualKaliStock = $wrapped->selectRaw('SUM(
    ROUND(
        (
            CAST(hrg_modal AS DECIMAL(15,2))
            + (CAST(hrg_modal AS DECIMAL(15,2)) * CAST(profit AS DECIMAL(15,2)) / 100)
        ) / 1000
    ) * 1000 * total_stok
) as total')->value('total');

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data,
            'total' => [
                'hrg_modal'              => $totalHargaModal ?? 0,
                'harga_jual'             => $totalHargaJual ?? 0,
                'stock'                  => $totalStock,
                'hrg_modal_kali_stock'   => $totalHargaModalKaliStock ?? 0,
                'harga_jual_kali_stock'  => $totalHargaJualKaliStock ?? 0,
            ]
        ]);
    }

    public function store(StoreProdukRequest $request)
    {
        $path = null;
        if ($request->hasFile('foto')) {
            // Buat nama unik
            $fileName = time() . '_' . uniqid() . '.' . $request->foto->extension();

            // Simpan di storage/app/public/foto_produk
            $path = $request->foto->storeAs('foto_produk', $fileName, 'public');
        }

        if ($request->kode) {
            $kode = $request->kode;
        } else {
            $kode = '9' . str_pad(mt_rand(0, 999999999999), 12, '0', STR_PAD_LEFT);
        }

        $produk = Produk::create([
            'uuid_kategori' => $request->uuid_kategori,
            'uuid_suplayer' => $request->uuid_suplayer,
            'sub_kategori' => $request->sub_kategori,
            'kode' => $kode,
            'nama_barang' => $request->nama_barang,
            'merek' => $request->merek,
            'hrg_modal' => preg_replace('/\D/', '', $request->hrg_modal),
            'profit' => $request->profit,
            'minstock' => $request->minstock,
            'maxstock' => $request->maxstock,
            'satuan' => $request->satuan,
            'profit_a' => $request->profit_a,
            'profit_b' => $request->profit_b,
            'profit_c' => $request->profit_c,
            'foto' => $path,
            'created_by' => Auth::user()->nama
        ]);

        PriceHistory::create([
            'uuid_produk' => $produk->uuid,
            'harga' => preg_replace('/\D/', '', $request->hrg_modal),
        ]);

        return response()->json(['status' => 'success']);
    }

    public function store_opname(Request $store)
    {
        $produk = Produk::where('uuid', $store->uuid_produk)->first();

        if (!$produk) {
            return response()->json(['status' => 'error', 'message' => 'Produk tidak ditemukan'], 404);
        }

        // Hitung stok sistem saat ini (akumulasi mutasi di stok_histories)
        $stok_sistem = StokHistory::where('uuid_produk', $produk->uuid)
            ->sum('stock'); // langsung sum, karena isi stock adalah mutasi (+/-)

        // Stok fisik hasil opname
        $stok_fisik = (int) $store->stock;

        // Hitung selisih antara fisik dengan sistem
        $selisih = $stok_fisik - $stok_sistem;

        // Simpan data opname
        Opname::create([
            'uuid_produk' => $produk->uuid,
            'uuid_user'   => Auth::user()->uuid,
            'stock'       => $stok_fisik,
            'keterangan'  => $store->keterangan,
        ]);

        // Jika tidak ada selisih, selesai
        if ($selisih == 0) {
            return response()->json(['status' => 'success']);
        }

        // Simpan pergerakan stok sebagai mutasi
        StokHistory::create([
            'uuid_produk' => $produk->uuid,
            'stock'       => $selisih, // hanya selisih, bisa positif atau negatif
        ]);

        if ($selisih != 0) {
            WirehouseStock::create([
                'uuid_produk'    => $produk->uuid,
                'qty'            => $store->stock,
                'jenis'          => $selisih > 0 ? 'masuk' : 'keluar',
                'sumber'         => 'opname',
                'keterangan'     => 'Penyesuaian stok opname #' . $produk->nama_barang,
            ]);
        }

        return response()->json(['status' => 'success']);
    }

    public function edit($params)
    {
        return response()->json(Produk::where('uuid', $params)->first());
    }

    public function update(UpdateProdukRequest $update, $params)
    {
        $produk = Produk::where('uuid', $params)->first();

        // Ambil harga modal lama & baru
        $hargaModalLama = (int) $produk->hrg_modal;
        $hargaModalBaru = (int) preg_replace('/\D/', '', $update->hrg_modal);

        if ($update->hasFile('foto')) {
            // Hapus foto lama jika ada
            if ($produk->foto && Storage::disk('public')->exists($produk->foto)) {
                Storage::disk('public')->delete($produk->foto);
            }

            // Simpan foto baru
            $fileName = time() . '_' . uniqid() . '.' . $update->foto->extension();
            $path = $update->foto->storeAs('foto_produk', $fileName, 'public');

            $produk->foto = $path;
        }

        $produk->update([
            'uuid_kategori' => $update->uuid_kategori,
            'uuid_suplayer' => $update->uuid_suplayer,
            'sub_kategori' => $update->sub_kategori,
            'kode' => $update->kode,
            'nama_barang' => $update->nama_barang,
            'merek' => $update->merek,
            'hrg_modal' => preg_replace('/\D/', '', $update->hrg_modal),
            'profit' => $update->profit,
            'minstock' => $update->minstock,
            'maxstock' => $update->maxstock,
            'satuan' => $update->satuan,
            'profit_a' => $update->profit_a,
            'profit_b' => $update->profit_b,
            'profit_c' => $update->profit_c,
            'update_by' => Auth::user()->nama
        ]);

        // Tambahkan price history hanya jika modal berubah
        if ($hargaModalLama !== $hargaModalBaru) {
            PriceHistory::create([
                'uuid_produk' => $produk->uuid,
                'harga' => $hargaModalBaru,
            ]);
        }

        return response()->json(['status' => 'success']);
    }

    public function delete($params)
    {
        $produk = Produk::where('uuid', $params)->first();

        // Cek apakah produk sudah pernah dipakai di detail pembelian
        $cekDetail = DetailPembelian::where('uuid_produk', $produk->uuid)->exists();

        if ($cekDetail) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Produk tidak bisa dihapus karena sudah tercatat di pembelian.'
            ], 400);
        }

        // Hapus foto jika ada
        if ($produk->foto && Storage::disk('public')->exists($produk->foto)) {
            Storage::disk('public')->delete($produk->foto);
        }

        // Hapus data produk
        $produk->delete();
        PriceHistory::where('uuid_produk', $produk->uuid)->delete();
        return response()->json(['status' => 'success']);
    }

    public function kartu_stock($params)
    {
        $produk = Produk::where('uuid', $params)->first();
        $module = 'Kartu Stock ' . $produk->nama_barang;

        return view('pages.produk.kartustock', compact('module', 'produk'));
    }

    public function get_kartu_stock(Request $request, $params)
    {
        $produk = Produk::where('uuid', $params)->first();
        $columns = [
            'wirehouse_stocks.uuid',
            'wirehouse_stocks.uuid_produk',
            'wirehouse_stocks.qty',
            'wirehouse_stocks.jenis',
            'wirehouse_stocks.sumber',
            'wirehouse_stocks.keterangan',
            'produks.nama_barang as nama_barang',
        ];

        // 🔹 Filter tanggal default bulan berjalan
        $tanggal_awal  = $request->get('tanggal_awal', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $tanggal_akhir = $request->get('tanggal_akhir', Carbon::now()->endOfMonth()->format('Y-m-d'));

        // Hitung total data tanpa filter
        $totalData = WirehouseStock::where('uuid_produk', $produk->uuid)->count();

        // Query utama
        $query = WirehouseStock::select($columns)
            ->join('produks', 'produks.uuid', '=', 'wirehouse_stocks.uuid_produk')
            ->where('wirehouse_stocks.uuid_produk', $produk->uuid)
            ->whereBetween('wirehouse_stocks.created_at', [
                Carbon::parse($tanggal_awal)->startOfDay(),
                Carbon::parse($tanggal_akhir)->endOfDay()
            ]);

        // Searching
        if (!empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search, $columns) {
                foreach ($columns as $column) {
                    $colName = explode(' as ', $column)[0];
                    $q->orWhere($colName, 'like', "%{$search}%");
                }
            });
        }

        // Hitung total data setelah filter
        $totalFiltered = $query->count();

        // Sorting
        if (!empty($request->order)) {
            $orderCol = explode(' as ', $columns[$request->order[0]['column']])[0];
            $orderDir = $request->order[0]['dir'];
            $query->orderBy($orderCol, $orderDir);
        } else {
            $query->latest('wirehouse_stocks.created_at');
        }

        // Pagination
        $query->skip($request->start)->take($request->length);

        // Ambil data
        $data = $query->get();

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ]);
    }

    public function cetakBarcode(Request $request, $params)
    {
        $produk = Produk::where('uuid', $params)->firstOrFail();

        $jumlah = (int) $request->input('jumlah', 1);
        if ($jumlah % 2 != 0) $jumlah++; // genapkan

        // Setting ukuran label (mm)
        $dpi = 203; // DPI printer Zebra umum
        $labelWidthMM  = 33;
        $labelHeightMM = 15;

        // Konversi mm -> dot
        $singleWidth = round($labelWidthMM * ($dpi / 25.4));
        $labelHeight = round($labelHeightMM * ($dpi / 25.4));

        // Margin fixed
        $marginX = 5;
        $marginY = 10;

        // Data produk
        $nama   = strtoupper($produk->nama_barang);
        $harga  = round($produk->hrg_modal + ($produk->hrg_modal * $produk->profit / 100), -3);
        $harga  = number_format($harga, 0, ',', '.');
        $barcode = $produk->kode;

        // Perhitungan tinggi teks nama barang
        $fontHeight   = 16;   // tinggi font (dot)
        $charsPerLine = 17;   // kira2 muat 14 huruf per baris
        $calcLines    = ceil(mb_strlen($nama) / $charsPerLine);

        // maksimal 2 baris, minimal 1
        $lines = min(2, max(1, $calcLines));

        // kalau lebih dari 2 → potong teks
        if ($calcLines > 2) {
            $nama = mb_substr($nama, 0, $charsPerLine * 2);
        }

        // Hitung posisi barcode berdasarkan jumlah baris nama
        if ($lines == 1) {
            $barcodeYOffset = ($fontHeight * 1) + 3; // 1 baris → agak naik
        } else {
            $barcodeYOffset = ($fontHeight * 2) + 4;  // 2 baris → lebih turun
        }

        // Posisi harga setelah barcode
        $hargaYOffset = $barcodeYOffset + 55;

        $zpl = "";
        for ($i = 0; $i < $jumlah; $i += 2) {
            $zpl .= "^XA\n^CI28\n";
            $zpl .= "^PW" . ($singleWidth * 2) . "\n";
            $zpl .= "^LL$labelHeight\n";

            // ------------------------
            // KOLOM KIRI
            // ------------------------
            $zpl .= "
            ^FO" . ($marginX) . "," . ($marginY) . "
            ^A0N,$fontHeight,$fontHeight
            ^FB" . ($singleWidth - 20) . ",$lines,0,C,0
            ^FD$nama^FS

            ^BY1,2,35
            ^FO" . ($marginX + 15) . "," . ($marginY + $barcodeYOffset) . "^BCN,35,Y,N,N^FD>:$barcode^FS

            ^FO" . ($marginX) . "," . ($marginY + $hargaYOffset) . "
^A0N,27,23
^FB" . ($singleWidth - 20) . ",1,0,C,0
^FDRp. $harga^FS
        ";

            // ------------------------
            // KOLOM KANAN
            // ------------------------
            $xOffset = $singleWidth + 30 + $marginX;
            $zpl .= "
            ^FO$xOffset," . ($marginY) . "
            ^A0N,$fontHeight,$fontHeight
            ^FB" . ($singleWidth - 20) . ",$lines,0,C,0
            ^FD$nama^FS

            ^BY1,2,35
            ^FO" . ($xOffset + 15) . "," . ($marginY + $barcodeYOffset) . "^BCN,35,Y,N,N^FD>:$barcode^FS

            ^FO" . ($xOffset) . "," . ($marginY + $hargaYOffset) . "
^A0N,27,23
^FB" . ($singleWidth - 20) . ",1,0,C,0
^FDRp. $harga^FS
        ";

            $zpl .= "^XZ\n";
        }

        // Simpan file sementara
        $tmpFile = tempnam(sys_get_temp_dir(), 'zpl');
        file_put_contents($tmpFile, $zpl);

        // Kirim ke printer (pakai raw biar tidak diubah driver)
        exec("lp -d ZEBRA_RAW -o raw " . escapeshellarg($tmpFile));

        return response()->json([
            'success' => true,
            'message' => "Label produk {$produk->nama_barang} berhasil dicetak ($jumlah label)"
        ]);
    }

    public function getKategori()
    {
        $kategori = Kategori::all();
        return response()->json([
            'success' => true,
            'data' => $kategori
        ]);
    }

    public function getProdukByKategori($uuid_kategori)
    {
        $produk = Produk::select(
            'produks.nama_barang',
            DB::raw("
                (
                    SELECT COALESCE(SUM(sh.stock), 0)
                    FROM stok_histories sh
                    WHERE sh.uuid_produk = produks.uuid
                ) AS stok
            "),
            DB::raw("
                ROUND(
                    (
                        CAST(produks.hrg_modal AS DECIMAL(15,2))
                        + (CAST(produks.hrg_modal AS DECIMAL(15,2)) * CAST(produks.profit AS DECIMAL(15,2)) / 100)
                    ) / 1000
                ) * 1000 AS harga_jual
            ")
        )
            ->where('produks.uuid_kategori', $uuid_kategori)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $produk
        ]);
    }
}
