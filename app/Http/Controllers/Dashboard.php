<?php

namespace App\Http\Controllers;

use App\Models\Coa;
use App\Models\Costumer;
use App\Models\Jurnal;
use App\Models\KasirOutlet;
use App\Models\Outlet;
use App\Models\Penjualan;
use App\Models\PoOutlet;
use App\Models\Produk;
use App\Models\StatusBarang;
use App\Models\TargetPenjualan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Dashboard extends BaseController
{
    public function landing_page()
    {
        return view('welcome');
    }

    public function dashboard_admin()
    {
        $module = 'Dashboard';
        $produk = Produk::count();
        $costumer = Costumer::count();

        $bulan = date('m');
        $tahun = date('Y');

        $tanggal_awal = "01-$bulan-$tahun";
        $tanggal_akhir = date("t-$bulan-$tahun"); // t = last day of month

        $coas = Coa::whereIn('tipe', ['pendapatan', 'beban'])->get();

        $pendapatan = [];
        $beban = [];
        $total_pendapatan = 0;
        $total_beban = 0;

        /* ===============================
   HITUNG SALDO COA (pendapatan & beban)
   =============================== */
        foreach ($coas as $coa) {

            $saldo = Jurnal::where('uuid_coa', $coa->uuid)
                ->whereRaw(
                    "STR_TO_DATE(jurnals.tanggal, '%d-%m-%Y')
             BETWEEN STR_TO_DATE(?, '%d-%m-%Y')
             AND STR_TO_DATE(?, '%d-%m-%Y')",
                    [$tanggal_awal, $tanggal_akhir]
                )
                ->selectRaw("
            COALESCE(
                SUM(
                    CASE
                        WHEN jenis = 'debit' THEN nominal
                        WHEN jenis = 'kredit' THEN -nominal
                        ELSE 0
                    END
                ), 0
            ) as saldo
        ")
                ->value('saldo');

            if ($saldo == 0) continue;

            $nilai = abs($saldo);

            if ($coa->tipe === 'pendapatan') {
                $pendapatan[] = [
                    'kode' => $coa->kode,
                    'nama' => $coa->nama,
                    'total' => $nilai,
                ];
                $total_pendapatan += $nilai;
            }

            if ($coa->tipe === 'beban') {
                $beban[] = [
                    'kode' => $coa->kode,
                    'nama' => $coa->nama,
                    'total' => $nilai,
                ];
                $total_beban += $nilai;
            }
        }

        /* ===============================
   LABA BERSIH
   =============================== */
        $laba_bersih = $total_pendapatan - $total_beban;

        return view('dashboard.admin', compact('module', 'produk', 'costumer', 'laba_bersih'));
    }

    public function getDashboardPenjualanKasirHarian(Request $request)
    {
        $query = DB::table('penjualans')
            ->join('users', 'penjualans.uuid_user', '=', 'users.uuid')
            ->select(
                'penjualans.uuid',
                DB::raw('penjualans.tanggal_transaksi as tanggal'),
                'users.nama as kasir',
                'penjualans.pembayaran',
                'penjualans.discount',
                'penjualans.uuid_jasa'
            )
            ->orderBy('penjualans.tanggal_transaksi', 'desc');

        $transaksis = $query->get();

        $rekapTanggal = [];

        foreach ($transaksis as $trx) {
            $tanggalFormatted = $trx->tanggal;
            $keyKasir   = $tanggalFormatted . '_' . $trx->kasir;

            if (!isset($rekapTanggal[$tanggalFormatted])) {
                $rekapTanggal[$tanggalFormatted] = [
                    'tanggal' => $tanggalFormatted,
                    'kasir'   => []
                ];
            }

            if (!isset($rekapTanggal[$tanggalFormatted]['kasir'][$keyKasir])) {
                $rekapTanggal[$tanggalFormatted]['kasir'][$keyKasir] = [
                    'nama'          => $trx->kasir,
                    'modal'         => 0,
                    'penjualan'     => 0,
                    'jasa'          => 0,
                    'profit'        => 0,
                    'tunai'         => 0,
                    'non_tunai'     => 0,
                    'sub_total'     => 0,
                    'total'         => 0,
                    'target_profit' => 0,
                    'persentase'    => 0,
                    'selisih'       => 0,
                ];
            }

            // === Produk
            $produkTotals = DB::table('detail_penjualans')
                ->join('harga_backup_penjualans', 'detail_penjualans.uuid', '=', 'harga_backup_penjualans.uuid_detail_penjualan')
                ->where('uuid_penjualans', $trx->uuid)
                ->selectRaw('
                SUM(detail_penjualans.total_harga) as total_penjualan,
                SUM(detail_penjualans.total_harga) - SUM(harga_backup_penjualans.harga_modal * detail_penjualans.qty) as total_profit,
                SUM(harga_backup_penjualans.harga_modal * detail_penjualans.qty) as total_modal
            ')
                ->first();

            // === Jasa (perbaikan: normalisasi sebelum array_count_values) ===
            // === Jasa + Discount Jasa ===
            $totalJasa = 0;
            $discountJasa = 0;

            if (!empty($trx->uuid_jasa)) {
                $uuidJasa = is_string($trx->uuid_jasa)
                    ? json_decode($trx->uuid_jasa, true)
                    : $trx->uuid_jasa;

                if (!is_array($uuidJasa)) {
                    $uuidJasa = [$uuidJasa];
                }

                // Normalisasi
                $uuidJasa = array_map(function ($item) {
                    if (is_array($item) && isset($item['uuid'])) return (string) $item['uuid'];
                    if (is_object($item) && isset($item->uuid)) return (string) $item->uuid;
                    if (is_string($item) || is_int($item)) return (string) $item;
                    return null;
                }, $uuidJasa);

                $uuidJasa = array_filter($uuidJasa);

                if (!empty($uuidJasa)) {
                    $counts = array_count_values($uuidJasa);

                    $hargaJasa = DB::table('jasas')
                        ->whereIn('uuid', array_keys($counts))
                        ->pluck('harga', 'uuid');

                    foreach ($counts as $uuid => $qty) {
                        $harga = isset($hargaJasa[$uuid]) ? (float) $hargaJasa[$uuid] : 0;
                        $totalJasa += $harga * $qty;
                    }
                }
            }

            // === Discount Jasa ===
            if (!empty($trx->discount)) {
                $disc = $trx->discount;

                if (str_contains($disc, '%')) {
                    $persen = floatval(str_replace('%', '', $disc));
                    $discountJasa = ($totalJasa * $persen) / 100;
                } else {
                    $discountJasa = floatval($disc);
                }
            }

            $totalJasaSetelahDisc = max(0, $totalJasa - $discountJasa);

            $modal      = $produkTotals->total_modal ?? 0;
            $penjualan  = $produkTotals->total_penjualan ?? 0;
            $profit     = $produkTotals->total_profit ?? 0;
            $sub_total = $penjualan + $totalJasaSetelahDisc;

            // === Update nilai kasir ===
            $rekapTanggal[$tanggalFormatted]['kasir'][$keyKasir]['modal']     += $modal;
            $rekapTanggal[$tanggalFormatted]['kasir'][$keyKasir]['penjualan'] += $penjualan;
            $rekapTanggal[$tanggalFormatted]['kasir'][$keyKasir]['jasa']      += $totalJasaSetelahDisc;
            $rekapTanggal[$tanggalFormatted]['kasir'][$keyKasir]['profit']    += $profit + $totalJasaSetelahDisc;
            $rekapTanggal[$tanggalFormatted]['kasir'][$keyKasir]['sub_total']     += $sub_total;

            if ($trx->pembayaran === 'Tunai') {
                $rekapTanggal[$tanggalFormatted]['kasir'][$keyKasir]['tunai'] += $sub_total;
            } else {
                $rekapTanggal[$tanggalFormatted]['kasir'][$keyKasir]['non_tunai'] += $sub_total;
            }
        }

        // Hitung target profit + persentase per kasir
        foreach ($rekapTanggal as $tanggal => &$group) {
            // Ambil target profit harian (pastikan format tanggal di DB cocok, misal 21-09-2025)
            $targetProfit = (int) DB::table('target_penjualans')
                ->where('tanggal', $tanggal)
                ->value('target') ?? 0;

            // Hitung total profit semua kasir hari ini
            $totalProfitTanggal = collect($group['kasir'])->sum('profit');
            $total = collect($group['kasir'])->sum('sub_total');

            foreach ($group['kasir'] as &$kasir) {
                $kasir['total'] = $total;
                $kasir['target_profit'] = $targetProfit;
                $kasir['persentase']    = $targetProfit > 0
                    ? round(($totalProfitTanggal / $targetProfit) * 100, 2)
                    : 0;
                // Selisih dihitung dari total profit semua kasir hari itu
                $kasir['selisih']       = $totalProfitTanggal - $targetProfit;
            }

            // Ubah kasir associative jadi array
            $group['kasir'] = array_values($group['kasir']);
        }

        // === Urutkan tanggal terbaru (desc) secara benar ===
        uksort($rekapTanggal, function ($a, $b) {
            return strtotime($b) <=> strtotime($a);
        });

        return response()->json([
            'status' => true,
            'data'   => array_values($rekapTanggal)
        ]);
    }

    public function getDashboardPenjualanKasirBulanan(Request $request)
    {
        // Ambil transaksi
        $query = DB::table('penjualans')
            ->join('users', 'penjualans.uuid_user', '=', 'users.uuid')
            ->select(
                'penjualans.uuid',
                'penjualans.tanggal_transaksi as tanggal',
                'users.nama as kasir',
                'penjualans.pembayaran',
                'penjualans.discount',
                'penjualans.uuid_jasa'
            )
            ->orderBy('tanggal', 'asc');

        $transaksis = $query->get();

        $rekapBulan = [];

        foreach ($transaksis as $trx) {
            // === Format tanggal (d-m-Y)
            $tanggalFormatted = \Carbon\Carbon::parse($trx->tanggal)->format('d-m-Y');

            // === Buat key bulanan
            $carbonDate = \Carbon\Carbon::createFromFormat('d-m-Y', $tanggalFormatted);
            $bulanKey   = $carbonDate->format('m-Y');
            $bulanLabel = $carbonDate->translatedFormat('F Y'); // Contoh: September 2025

            $keyKasir = $bulanKey . '_' . $trx->kasir;

            if (!isset($rekapBulan[$bulanKey])) {
                $rekapBulan[$bulanKey] = [
                    'bulan' => $bulanLabel,
                    'kasir' => []
                ];
            }

            if (!isset($rekapBulan[$bulanKey]['kasir'][$keyKasir])) {
                $rekapBulan[$bulanKey]['kasir'][$keyKasir] = [
                    'nama'          => $trx->kasir,
                    'modal'         => 0,
                    'penjualan'     => 0,
                    'jasa'          => 0,
                    'profit'        => 0,
                    'tunai'         => 0,
                    'non_tunai'     => 0,
                    'sub_total'     => 0,
                    'total'     => 0,
                    'target_profit' => 0,
                    'persentase'    => 0,
                    'selisih'       => 0,
                ];
            }

            // === Produk
            $produkTotals = DB::table('detail_penjualans')
                ->join('harga_backup_penjualans', 'detail_penjualans.uuid', '=', 'harga_backup_penjualans.uuid_detail_penjualan')
                ->where('uuid_penjualans', $trx->uuid)
                ->selectRaw('
                SUM(detail_penjualans.total_harga) as total_penjualan,
                SUM(detail_penjualans.total_harga) - SUM(harga_backup_penjualans.harga_modal * detail_penjualans.qty) as total_profit,
                SUM(harga_backup_penjualans.harga_modal * detail_penjualans.qty) as total_modal
            ')
                ->first();

            // === Jasa (perbaikan: normalisasi sebelum array_count_values) ===
            $totalJasa = 0;
            $discountJasa = 0;

            if (!empty($trx->uuid_jasa)) {
                $uuidJasa = is_string($trx->uuid_jasa)
                    ? json_decode($trx->uuid_jasa, true)
                    : $trx->uuid_jasa;

                if (!is_array($uuidJasa)) {
                    $uuidJasa = [$uuidJasa];
                }

                // Normalisasi
                $uuidJasa = array_map(function ($item) {
                    if (is_array($item) && isset($item['uuid'])) return (string) $item['uuid'];
                    if (is_object($item) && isset($item->uuid)) return (string) $item->uuid;
                    if (is_string($item) || is_int($item)) return (string) $item;
                    return null;
                }, $uuidJasa);

                $uuidJasa = array_filter($uuidJasa);

                if (!empty($uuidJasa)) {
                    $counts = array_count_values($uuidJasa);

                    $hargaJasa = DB::table('jasas')
                        ->whereIn('uuid', array_keys($counts))
                        ->pluck('harga', 'uuid');

                    foreach ($counts as $uuid => $qty) {
                        $harga = isset($hargaJasa[$uuid]) ? (float) $hargaJasa[$uuid] : 0;
                        $totalJasa += $harga * $qty;
                    }
                }
            }

            // === Discount Jasa ===
            if (!empty($trx->discount)) {
                $disc = $trx->discount;

                if (str_contains($disc, '%')) {
                    $persen = floatval(str_replace('%', '', $disc));
                    $discountJasa = ($totalJasa * $persen) / 100;
                } else {
                    $discountJasa = floatval($disc);
                }
            }

            $totalJasaSetelahDisc = max(0, $totalJasa - $discountJasa);

            $modal      = $produkTotals->total_modal ?? 0;
            $penjualan  = $produkTotals->total_penjualan ?? 0;
            $profit     = $produkTotals->total_profit ?? 0;
            $sub_total      = $penjualan + $totalJasaSetelahDisc;

            // Update nilai kasir
            $rekapBulan[$bulanKey]['kasir'][$keyKasir]['modal']     += $modal;
            $rekapBulan[$bulanKey]['kasir'][$keyKasir]['penjualan'] += $penjualan;
            $rekapBulan[$bulanKey]['kasir'][$keyKasir]['jasa']      += $totalJasaSetelahDisc;
            $rekapBulan[$bulanKey]['kasir'][$keyKasir]['profit']    += $profit + $totalJasaSetelahDisc;
            $rekapBulan[$bulanKey]['kasir'][$keyKasir]['sub_total']     += $sub_total;

            if ($trx->pembayaran === 'Tunai') {
                $rekapBulan[$bulanKey]['kasir'][$keyKasir]['tunai'] += $sub_total;
            } else {
                $rekapBulan[$bulanKey]['kasir'][$keyKasir]['non_tunai'] += $sub_total;
            }
        }

        // === Hitung target profit bulanan
        foreach ($rekapBulan as $bulanKey => &$group) {
            $carbon = \Carbon\Carbon::createFromFormat('m-Y', $bulanKey);
            $awalBulan  = $carbon->copy()->startOfMonth()->format('Y-m-d');
            $akhirBulan = $carbon->copy()->endOfMonth()->format('Y-m-d');

            $targetProfit = (int) DB::table('target_penjualans')
                ->whereRaw("STR_TO_DATE(tanggal, '%d-%m-%Y') BETWEEN ? AND ?", [$awalBulan, $akhirBulan])
                ->sum('target');

            // Hitung total profit semua kasir dalam bulan ini
            $totalProfitBulan = collect($group['kasir'])->sum('profit');
            $total = collect($group['kasir'])->sum('sub_total');

            foreach ($group['kasir'] as &$kasir) {
                $kasir['total'] = $total;
                $kasir['target_profit'] = $targetProfit;
                $kasir['persentase']    = $targetProfit > 0 ? round(($totalProfitBulan / $targetProfit) * 100, 2) : 0;
                // Selisih dihitung dari total bulan, bukan per kasir
                $kasir['selisih']       = $totalProfitBulan - $targetProfit;
            }

            // Ubah kasir associative jadi array
            $group['kasir'] = array_values($group['kasir']);
        }

        uksort($rekapBulan, function ($a, $b) {
            [$bulanA, $tahunA] = explode('-', $a);
            [$bulanB, $tahunB] = explode('-', $b);

            if ($tahunA == $tahunB) {
                return $bulanB <=> $bulanA;
            }
            return $tahunB <=> $tahunA;
        });

        return response()->json([
            'status' => true,
            'data'   => array_values($rekapBulan)
        ]);
    }

    public function getProdukUnggul(Request $request)
    {
        // === Produk biasa ===
        $produkQuery = DB::table('detail_penjualans as dp')
            ->join('penjualans as p', 'dp.uuid_penjualans', '=', 'p.uuid')
            ->join('harga_backup_penjualans as hbp', 'dp.uuid', '=', 'hbp.uuid_detail_penjualan')
            ->join('produks as pr', 'dp.uuid_produk', '=', 'pr.uuid')
            ->select(
                'dp.uuid_produk as uuid_produk',
                'pr.nama_barang as nama_barang',
                DB::raw('SUM(dp.qty) as total_terjual'),
                DB::raw('SUM(dp.total_harga - hbp.harga_modal * dp.qty) as total_profit')
            )
            ->groupBy('dp.uuid_produk', 'pr.nama_barang');

        // === UNION produk + paket ===
        $query = DB::query()->fromSub($produkQuery, 'all_produk')
            ->select(
                'uuid_produk',
                'nama_barang',
                DB::raw('SUM(total_terjual) as total_terjual'),
                DB::raw('SUM(total_profit) as total_profit')
            )
            ->groupBy('uuid_produk', 'nama_barang');

        // Ambil Top 5
        $topLaku = (clone $query)->orderByDesc('total_terjual')->get();
        $topUntung = (clone $query)->orderByDesc('total_profit')->get();

        return response()->json([
            'top_laku'   => $topLaku,
            'top_untung' => $topUntung
        ]);
    }
}
