<?php

namespace App\Console\Commands;

use App\Mail\LaporanHarianMail;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class KirimEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:kirim-email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mengirim email laporan harian otomatis setiap malam jam 11.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tanggalHariIni = Carbon::now()->format('d-m-Y');
        // $tanggalHariIni = '20-11-2025';

        $emailTujuan = 'mmmaeroautomotive@gmail.com';
        // $emailTujuan = 'wm337708@gmail.com';

        $transaksis = DB::table('penjualans')
            ->join('users', 'penjualans.uuid_user', '=', 'users.uuid')
            ->where('penjualans.tanggal_transaksi', $tanggalHariIni)
            ->select(
                'penjualans.uuid',
                'users.nama as kasir',
                'penjualans.pembayaran',
                'penjualans.uuid_jasa'
            )
            ->get();

        if ($transaksis->isEmpty()) {
            $this->info("❌ Tidak ada transaksi pada tanggal {$tanggalHariIni}. Laporan tidak dikirim.");
            return;
        }

        $laporan = [];

        foreach ($transaksis as $trx) {
            // === Produk
            $produkTotals = DB::table('detail_penjualans')
                ->join('harga_backup_penjualans', 'detail_penjualans.uuid', '=', 'harga_backup_penjualans.uuid_detail_penjualan')
                ->where('uuid_penjualans', $trx->uuid)
                ->selectRaw('
                    SUM(detail_penjualans.total_harga) as total_penjualan,
                    SUM(detail_penjualans.total_harga) - SUM(harga_backup_penjualans.harga_modal * detail_penjualans.qty) as total_profit
                ')
                ->first();

            // === Jasa
            $totalJasa = 0;
            if (!empty($trx->uuid_jasa)) {
                $uuidJasa = is_array($trx->uuid_jasa)
                    ? $trx->uuid_jasa
                    : json_decode($trx->uuid_jasa, true);

                if (!empty($uuidJasa)) {
                    $counts = array_count_values($uuidJasa);

                    $hargaJasa = DB::table('jasas')
                        ->whereIn('uuid', array_keys($counts))
                        ->pluck('harga', 'uuid');

                    foreach ($counts as $uuid => $qty) {
                        $totalJasa += ($hargaJasa[$uuid] ?? 0) * $qty;
                    }
                }
            }

            $totalPenjualan = ($produkTotals->total_penjualan ?? 0)
                + $totalJasa;

            $totalProfit    = ($produkTotals->total_profit ?? 0)
                + $totalJasa;

            // === Kelompokkan per kasir ===
            if (!isset($laporan[$trx->kasir])) {
                $laporan[$trx->kasir] = [
                    'kasir' => $trx->kasir,
                    'tunai' => 0,
                    'non_tunai' => 0,
                    'profit' => 0,
                ];
            }

            if (strtolower($trx->pembayaran) === 'tunai') {
                $laporan[$trx->kasir]['tunai'] += $totalPenjualan;
            } else {
                $laporan[$trx->kasir]['non_tunai'] += $totalPenjualan;
            }

            $laporan[$trx->kasir]['profit'] += $totalProfit;
        }

        // Hitung total akhir per kasir
        $hasil = collect($laporan)->map(function ($item) {
            $item['total'] = $item['tunai'] + $item['non_tunai'];
            $target = 1500000;
            $item['persentase'] = $target > 0
                ? round(($item['profit'] / $target) * 100, 2)
                : 0;
            return $item;
        })->values()->toArray();

        // === Kirim Email ke Owner yang sesuai outlet ===
        Mail::to($emailTujuan)->send(
            new LaporanHarianMail($hasil, $tanggalHariIni)
        );

        $this->info("✅ Laporan terkirim ke {$emailTujuan}");

        $this->info('🎉 Semua laporan outlet sudah dikirim ke owner.');
    }
}
