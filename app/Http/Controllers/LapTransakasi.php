<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use App\Models\Penjualan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LapTransakasi extends BaseController
{
    public function index()
    {
        $module = 'Laporan Transaksi';
        return view('pages.laptransaksi.index', compact('module'));
    }

    public function get(Request $request)
    {
        $start = $request->tanggal_awal;
        $end   = $request->tanggal_akhir;

        $columns = [
            'p.no_bukti',
            'p.tanggal_transaksi',
            'p.pembayaran',
            'users.nama',
            'p.created_at'
        ];

        $totalData = Penjualan::count();

        /**
         * SUBQUERY JASA
         * Menggunakan JSON_TABLE untuk membaca array uuid_jasa dengan aman
         */
        $jasaSub = DB::table('penjualans')
            ->select(
                'penjualans.id',
                DB::raw('SUM(jasas.harga) AS total_jasa')
            )
            ->join(DB::raw("
            JSON_TABLE(
                penjualans.uuid_jasa,
                '$[*]' COLUMNS (
                    uuid_jasa VARCHAR(255) PATH '$'
                )
            ) AS jt
        "), DB::raw('1'), '=', DB::raw('1'))
            ->join('jasas', 'jasas.uuid', '=', 'jt.uuid_jasa')
            ->groupBy('penjualans.id');

        /**
         * QUERY UTAMA
         */
        $sub = DB::table('penjualans')
            ->leftJoin('detail_penjualans', 'penjualans.uuid', '=', 'detail_penjualans.uuid_penjualans')
            ->leftJoin('harga_backup_penjualans', 'harga_backup_penjualans.uuid_detail_penjualan', '=', 'detail_penjualans.uuid')
            ->leftJoinSub($jasaSub, 'jasa', function ($join) {
                $join->on('penjualans.id', '=', 'jasa.id');
            })
            ->select(
                'penjualans.*',

                // === TOTAL PRODUK ===
                DB::raw('COALESCE(SUM(detail_penjualans.total_harga),0) AS total_penjualan'),
                DB::raw('COALESCE(SUM(harga_backup_penjualans.harga_modal * detail_penjualans.qty),0) AS total_modal'),

                // === TOTAL JASA SEBELUM DISKON ===
                DB::raw('COALESCE(jasa.total_jasa, 0) AS total_jasa'),

                // === DISCOUNT JASA ===
                DB::raw("
                    CASE
                        WHEN penjualans.discount LIKE '%\%%'
                        THEN (COALESCE(jasa.total_jasa,0) * REPLACE(penjualans.discount, '%', '') / 100)
                        ELSE COALESCE(penjualans.discount, 0)
                    END AS discount_jasa
                "),

                // === TOTAL JASA SETELAH DISKON ===
                DB::raw("
                    COALESCE(jasa.total_jasa,0) -
                    CASE
                        WHEN penjualans.discount LIKE '%\%%'
                        THEN (COALESCE(jasa.total_jasa,0) * REPLACE(penjualans.discount, '%', '') / 100)
                        ELSE COALESCE(penjualans.discount, 0)
                    END AS total_jasa_setelah_diskon
                ")
            )
            ->groupBy('penjualans.id');

        if ($request->filled('tanggal_awal') && $request->filled('tanggal_akhir')) {

            $start = Carbon::createFromFormat('d-m-Y', $request->tanggal_awal)->format('Y-m-d');
            $end   = Carbon::createFromFormat('d-m-Y', $request->tanggal_akhir)->format('Y-m-d');

            $sub->whereBetween(
                DB::raw("STR_TO_DATE(penjualans.tanggal_transaksi, '%d-%m-%Y')"),
                [$start, $end]
            );
        }

        /**
         * WRAP SUBQUERY
         */
        $query = DB::table(DB::raw("({$sub->toSql()}) AS p"))
            ->mergeBindings($sub)
            ->leftJoin('users', 'p.uuid_user', '=', 'users.uuid')
            ->select('p.*', 'users.nama as nama_user')
            ->orderBy('p.created_at', 'desc');

        /**
         * SEARCH
         */
        if (!empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search, $columns) {
                foreach ($columns as $column) {
                    $q->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        /**
         * TOTAL FILTERED
         */
        $countQuery = DB::table(DB::raw("({$sub->toSql()}) AS p"))
            ->mergeBindings($sub)
            ->leftJoin('users', 'p.uuid_user', '=', 'users.uuid');

        if (!empty($request->search['value'])) {
            $search = $request->search['value'];
            $countQuery->where(function ($q) use ($search, $columns) {
                foreach ($columns as $column) {
                    $q->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        $totalFiltered = $countQuery->count();

        /**
         * PAGINATION
         */
        $data = $query
            ->skip($request->start)
            ->take($request->length)
            ->get();

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ]);
    }

    public function detail($params)
    {
        // ============================
        // DETAIL PRODUK
        // ============================
        $produkDetails = DB::table('detail_penjualans as dp')
            ->join('penjualans as p', 'dp.uuid_penjualans', '=', 'p.uuid')
            ->join('produks as pr', 'dp.uuid_produk', '=', 'pr.uuid')
            ->leftJoin('suplayers as s', 'pr.uuid_suplayer', '=', 's.uuid')
            ->leftJoin('kategoris as k', 'pr.uuid_kategori', '=', 'k.uuid')
            ->where('dp.uuid_penjualans', $params)   // <=== WAJIB
            ->select(
                'p.tanggal_transaksi',
                'p.no_bukti',
                'pr.nama_barang',
                'pr.merek',
                'k.nama_kategori',
                'pr.sub_kategori',
                's.nama as nama_suplier',
                'dp.qty',
                'dp.total_harga'
            );

        // ============================
        // GABUNGKAN & SORT BY NAMA BARANG
        // ============================
        $allDetails = $produkDetails
            ->orderBy('nama_barang', 'asc')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $allDetails
        ]);
    }

    public function export_excel(Request $request, $params = null)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // ===== Header (disesuaikan dengan data yang dipakai) =====
        $headers = [
            'A1' => 'Tanggal',
            'B1' => 'No Bukti',
            'C1' => 'Nama Barang',
            'D1' => 'Merek',
            'E1' => 'Kategori',
            'F1' => 'Sub Kategori',
            'G1' => 'Suplier',
            'H1' => 'Qty',
            'I1' => 'Total Produk',
            'J1' => 'Nama Jasa',
            'K1' => 'Harga Jasa',
            'L1' => 'Total Produk + Jasa',
        ];

        foreach ($headers as $col => $text) {
            $sheet->setCellValue($col, $text);
        }

        // ==== Ambil tanggal ====
        $start = $request->tanggal_awal;
        $end   = $request->tanggal_akhir;

        if ($start && $end) {
            $start = Carbon::createFromFormat('d-m-Y', $start)->format('Y-m-d');
            $end   = Carbon::createFromFormat('d-m-Y', $end)->format('Y-m-d');
        }

        /* ===========================
       DETAIL PRODUK
    =========================== */
        $produkDetails = DB::table('detail_penjualans as dp')
            ->join('penjualans as p', 'dp.uuid_penjualans', '=', 'p.uuid')
            ->join('produks as pr', 'dp.uuid_produk', '=', 'pr.uuid')
            ->leftJoin('suplayers as s', 'pr.uuid_suplayer', '=', 's.uuid')
            ->leftJoin('kategoris as k', 'pr.uuid_kategori', '=', 'k.uuid')
            ->select(
                'p.tanggal_transaksi',
                'p.no_bukti',
                'pr.nama_barang',
                'pr.merek',
                'k.nama_kategori',
                'pr.sub_kategori',
                's.nama as nama_suplier',
                'dp.qty',
                'dp.total_harga'
            );

        // Filter outlet
        if ($request->uuid_outlet) {
            $produkDetails->where('p.uuid_outlet', $request->uuid_outlet);
        }

        // Filter tanggal
        if ($start && $end) {
            $produkDetails->whereBetween(
                DB::raw("STR_TO_DATE(p.tanggal_transaksi, '%d-%m-%Y')"),
                [$start, $end]
            );
        }

        $jasaPerPenjualan = DB::table('penjualans')
            ->leftJoin(DB::raw("
        JSON_TABLE(
            penjualans.uuid_jasa,
            '$[*]' COLUMNS (
                uuid_jasa VARCHAR(255) PATH '$'
            )
        ) jt
    "), DB::raw('1'), '=', DB::raw('1'))
            ->leftJoin('jasas', 'jasas.uuid', '=', 'jt.uuid_jasa')
            ->select(
                'penjualans.no_bukti',
                DB::raw('GROUP_CONCAT(jasas.nama SEPARATOR ", ") AS nama_jasa'),
                DB::raw('SUM(jasas.harga) AS total_jasa')
            )
            ->groupBy('penjualans.no_bukti')
            ->get()
            ->keyBy('no_bukti');


        /* ===========================
       GABUNGKAN & SORT
    =========================== */
        $allDetails = $produkDetails->get();

        // SORT: tanggal DESC, no_bukti DESC
        $allDetails = $allDetails->sort(function ($a, $b) {
            $dateA = Carbon::createFromFormat('d-m-Y', $a->tanggal_transaksi);
            $dateB = Carbon::createFromFormat('d-m-Y', $b->tanggal_transaksi);

            if ($dateA->eq($dateB)) {
                return strcmp($b->no_bukti, $a->no_bukti);
            }

            return $dateB <=> $dateA;
        })->values();

        // ===== Isi Excel =====
        $lastNoBukti = null;
        $row = 2;
        foreach ($allDetails as $d) {

            $isFirstRow = $lastNoBukti !== $d->no_bukti;

            $jasa = $isFirstRow
                ? ($jasaPerPenjualan[$d->no_bukti] ?? null)
                : null;

            $totalJasa  = $isFirstRow ? ($jasa->total_jasa ?? 0) : 0;
            $namaJasa   = $isFirstRow ? ($jasa->nama_jasa ?? '-') : '-';
            $totalAkhir = $d->total_harga + $totalJasa;

            $sheet->setCellValue('A' . $row, $d->tanggal_transaksi);
            $sheet->setCellValue('B' . $row, $d->no_bukti);
            $sheet->setCellValue('C' . $row, $d->nama_barang);
            $sheet->setCellValue('D' . $row, $d->merek);
            $sheet->setCellValue('E' . $row, $d->nama_kategori);
            $sheet->setCellValue('F' . $row, $d->sub_kategori);
            $sheet->setCellValue('G' . $row, $d->nama_suplier);
            $sheet->setCellValue('H' . $row, $d->qty);
            $sheet->setCellValue('I' . $row, $d->total_harga);
            $sheet->setCellValue('J' . $row, $namaJasa);
            $sheet->setCellValue('K' . $row, $totalJasa);
            $sheet->setCellValue('L' . $row, $totalAkhir);

            foreach (['I', 'K', 'L'] as $col) {
                $sheet->getStyle($col . $row)
                    ->getNumberFormat()
                    ->setFormatCode('"Rp" #,##0');
            }

            $lastNoBukti = $d->no_bukti;
            $row++;
        }

        // Auto width (sampai kolom I)
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // ===== TOTAL =====
        $sheet->mergeCells("A{$row}:K{$row}");
        $sheet->setCellValue("A{$row}", 'TOTAL');
        $sheet->setCellValue("L{$row}", "=SUM(L2:L" . ($row - 1) . ")");

        $sheet->getStyle("L{$row}")
            ->getNumberFormat()
            ->setFormatCode('"Rp" #,##0');

        // ===== Style Header =====
        $sheet->getStyle('A1:L1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['rgb' => '4F81BD']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                ]
            ]
        ]);

        // ===== Style Footer =====
        $sheet->getStyle("A{$row}:L{$row}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['rgb' => 'D9D9D9']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                ]
            ]
        ]);

        // Border data
        $sheet->getStyle("A2:L" . ($row - 1))
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Download
        $fileName = 'penjualan-export.xlsx';
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}
