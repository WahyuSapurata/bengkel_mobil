<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::group([
    'prefix' => 'login',
    'middleware' => ['guest'],
    'as' => 'login.'
], function () {
    Route::get('/login-akun', [App\Http\Controllers\Auth::class, 'show'])->name('login-akun');
    Route::post('/login-proses', [App\Http\Controllers\Auth::class, 'login_proses'])->name('login-proses');
});

Route::group([
    'prefix' => 'admin',
    'middleware' => ['auth'],
    'as' => 'admin.'
], function () {
    Route::get('/dashboard-admin', [App\Http\Controllers\Dashboard::class, 'dashboard_admin'])->name('dashboard-admin');

    Route::get('/get-penjualan-kasir-harian', [App\Http\Controllers\Dashboard::class, 'getDashboardPenjualanKasirHarian'])->name('get-penjualan-kasir-harian');
    Route::get('/get-penjualan-kasir-bulanan', [App\Http\Controllers\Dashboard::class, 'getDashboardPenjualanKasirBulanan'])->name('get-penjualan-kasir-bulanan');

    Route::get('/get-penjualan-terlaku', [App\Http\Controllers\Dashboard::class, 'getProdukUnggul'])->name('get-penjualan-terlaku');

    Route::prefix('setup')->group(function () {
        Route::get('/data-pengguna', [App\Http\Controllers\DataPengguna::class, 'index'])->name('data-pengguna');
        Route::get('/data-pengguna-get', [App\Http\Controllers\DataPengguna::class, 'get'])->name('data-pengguna-get');
        Route::post('/data-pengguna-store', [App\Http\Controllers\DataPengguna::class, 'store'])->name('data-pengguna-store');
        Route::get('/data-pengguna-edit/{params}', [App\Http\Controllers\DataPengguna::class, 'edit'])->name('data-pengguna-edit');
        Route::post('/data-pengguna-update/{params}', [App\Http\Controllers\DataPengguna::class, 'update'])->name('data-pengguna-update');
        Route::delete('/data-pengguna-delete/{params}', [App\Http\Controllers\DataPengguna::class, 'delete'])->name('data-pengguna-delete');

        Route::get('/target-penjualan', [App\Http\Controllers\TargetPenjualanController::class, 'index'])->name('target-penjualan');
        Route::get('/target-penjualan-get', [App\Http\Controllers\TargetPenjualanController::class, 'get'])->name('target-penjualan-get');
        Route::post('/target-penjualan-store', [App\Http\Controllers\TargetPenjualanController::class, 'store'])->name('target-penjualan-store');
        Route::get('/target-penjualan-edit/{params}', [App\Http\Controllers\TargetPenjualanController::class, 'edit'])->name('target-penjualan-edit');
        Route::put('/target-penjualan-update/{params}', [App\Http\Controllers\TargetPenjualanController::class, 'update'])->name('target-penjualan-update');
        Route::delete('/target-penjualan-delete/{params}', [App\Http\Controllers\TargetPenjualanController::class, 'delete'])->name('target-penjualan-delete');
    });

    Route::prefix('master-data')->group(function () {
        Route::get('/kategori', [App\Http\Controllers\KategoriController::class, 'index'])->name('kategori');
        Route::get('/kategori-get', [App\Http\Controllers\KategoriController::class, 'get'])->name('kategori-get');
        Route::post('/kategori-store', [App\Http\Controllers\KategoriController::class, 'store'])->name('kategori-store');
        Route::get('/kategori-edit/{params}', [App\Http\Controllers\KategoriController::class, 'edit'])->name('kategori-edit');
        Route::put('/kategori-update/{params}', [App\Http\Controllers\KategoriController::class, 'update'])->name('kategori-update');
        Route::delete('/kategori-delete/{params}', [App\Http\Controllers\KategoriController::class, 'delete'])->name('kategori-delete');

        Route::get('/suplayer', [App\Http\Controllers\SuplayerController::class, 'index'])->name('suplayer');
        Route::get('/suplayer-get', [App\Http\Controllers\SuplayerController::class, 'get'])->name('suplayer-get');
        Route::post('/suplayer-store', [App\Http\Controllers\SuplayerController::class, 'store'])->name('suplayer-store');
        Route::get('/suplayer-edit/{params}', [App\Http\Controllers\SuplayerController::class, 'edit'])->name('suplayer-edit');
        Route::put('/suplayer-update/{params}', [App\Http\Controllers\SuplayerController::class, 'update'])->name('suplayer-update');
        Route::delete('/suplayer-delete/{params}', [App\Http\Controllers\SuplayerController::class, 'delete'])->name('suplayer-delete');

        Route::get('/jasa', [App\Http\Controllers\JasaController::class, 'index'])->name('jasa');
        Route::get('/jasa-get', [App\Http\Controllers\JasaController::class, 'get'])->name('jasa-get');
        Route::post('/jasa-store', [App\Http\Controllers\JasaController::class, 'store'])->name('jasa-store');
        Route::get('/jasa-edit/{params}', [App\Http\Controllers\JasaController::class, 'edit'])->name('jasa-edit');
        Route::put('/jasa-update/{params}', [App\Http\Controllers\JasaController::class, 'update'])->name('jasa-update');
        Route::delete('/jasa-delete/{params}', [App\Http\Controllers\JasaController::class, 'delete'])->name('jasa-delete');

        Route::get('/produk', [App\Http\Controllers\ProdukController::class, 'index'])->name('produk');
        Route::get('/produk-get', [App\Http\Controllers\ProdukController::class, 'get'])->name('produk-get');
        Route::post('/produk-store', [App\Http\Controllers\ProdukController::class, 'store'])->name('produk-store');
        Route::get('/produk-edit/{params}', [App\Http\Controllers\ProdukController::class, 'edit'])->name('produk-edit');
        Route::post('/produk-update/{params}', [App\Http\Controllers\ProdukController::class, 'update'])->name('produk-update');
        Route::delete('/produk-delete/{params}', [App\Http\Controllers\ProdukController::class, 'delete'])->name('produk-delete');

        Route::get('/get-sub-kategori/{params}', [App\Http\Controllers\ProdukController::class, 'getSubKategori'])->name('get-sub-kategori');

        Route::get('/price-history/{params}', [App\Http\Controllers\ProdukController::class, 'price_history'])->name('price-history');
        Route::get('/get-price-history/{params}', [App\Http\Controllers\ProdukController::class, 'get_price_history'])->name('get-price-history');

        Route::get('/kartu-stock/{params}', [App\Http\Controllers\ProdukController::class, 'kartu_stock'])->name('kartu-stock');
        Route::get('/get-kartu-stock/{params}', [App\Http\Controllers\ProdukController::class, 'get_kartu_stock'])->name('get-kartu-stock');

        Route::get('/opname-stock/{params}', [App\Http\Controllers\ProdukController::class, 'opname_stock'])->name('opname-stock');
        Route::post('/store_opname', [App\Http\Controllers\ProdukController::class, 'store_opname'])->name('store_opname');

        Route::post('/cetak-barcode/{params}', [App\Http\Controllers\ProdukController::class, 'cetakBarcode'])->name('cetak-barcode');

        Route::get('/produk-price/{params}', [App\Http\Controllers\ProdukPriceController::class, 'index'])->name('produk-price');
        Route::get('/produk-price-get/{params}', [App\Http\Controllers\ProdukPriceController::class, 'get'])->name('produk-price-get');
        Route::post('/produk-price-store', [App\Http\Controllers\ProdukPriceController::class, 'store'])->name('produk-price-store');
        Route::get('/produk-price-edit/{params}', [App\Http\Controllers\ProdukPriceController::class, 'edit'])->name('produk-price-edit');
        Route::post('/produk-price-update/{params}', [App\Http\Controllers\ProdukPriceController::class, 'update'])->name('produk-price-update');
        Route::delete('/produk-price-delete/{params}', [App\Http\Controllers\ProdukPriceController::class, 'delete'])->name('produk-price-delete');

        Route::get('/kasir', [App\Http\Controllers\DataKasirController::class, 'index'])->name('kasir');
        Route::get('/kasir-get', [App\Http\Controllers\DataKasirController::class, 'get'])->name('kasir-get');
        Route::post('/kasir-store', [App\Http\Controllers\DataKasirController::class, 'store'])->name('kasir-store');
        Route::get('/kasir-edit/{params}', [App\Http\Controllers\DataKasirController::class, 'edit'])->name('kasir-edit');
        Route::post('/kasir-update/{params}', [App\Http\Controllers\DataKasirController::class, 'update'])->name('kasir-update');
        Route::delete('/kasir-delete/{params}', [App\Http\Controllers\DataKasirController::class, 'delete'])->name('kasir-delete');

        Route::get('/costumer', [App\Http\Controllers\CostumerController::class, 'index'])->name('costumer');
        Route::get('/costumer-get', [App\Http\Controllers\CostumerController::class, 'get'])->name('costumer-get');
        Route::post('/costumer-store', [App\Http\Controllers\CostumerController::class, 'store'])->name('costumer-store');
        Route::get('/costumer-edit/{params}', [App\Http\Controllers\CostumerController::class, 'edit'])->name('costumer-edit');
        Route::post('/costumer-update/{params}', [App\Http\Controllers\CostumerController::class, 'update'])->name('costumer-update');
        Route::delete('/costumer-delete/{params}', [App\Http\Controllers\CostumerController::class, 'delete'])->name('costumer-delete');
    });

    Route::prefix('transaksi')->group(function () {
        Route::get('/pembelian', [App\Http\Controllers\PembelianController::class, 'index'])->name('pembelian');
        Route::get('/pembelian-get', [App\Http\Controllers\PembelianController::class, 'get'])->name('pembelian-get');
        Route::post('/pembelian-store', [App\Http\Controllers\PembelianController::class, 'store'])->name('pembelian-store');
        Route::get('/pembelian-edit/{params}', [App\Http\Controllers\PembelianController::class, 'edit'])->name('pembelian-edit');
        Route::post('/pembelian-update/{params}', [App\Http\Controllers\PembelianController::class, 'update'])->name('pembelian-update');
        Route::delete('/pembelian-delete/{params}', [App\Http\Controllers\PembelianController::class, 'delete'])->name('pembelian-delete');

        Route::get('/pembelian-get-produk-by-suplayer/{params}', [App\Http\Controllers\PembelianController::class, 'getProdukBySuplayer'])->name('pembelian-get-produk-by-suplayer');

        Route::get('/hutang', [App\Http\Controllers\HutangController::class, 'index'])->name('hutang');
        Route::get('/hutang-get', [App\Http\Controllers\HutangController::class, 'get'])->name('hutang-get');
        Route::get('/hutang-edit/{params}', [App\Http\Controllers\HutangController::class, 'edit'])->name('hutang-edit');
        Route::post('/hutang-update/{params}', [App\Http\Controllers\HutangController::class, 'update'])->name('hutang-update');
        Route::delete('/hutang-delete/{params}', [App\Http\Controllers\HutangController::class, 'delete'])->name('hutang-delete');
    });

    Route::prefix('accounting')->group(function () {
        Route::get('/pengeluaran', [App\Http\Controllers\PengeluaranController::class, 'index'])->name('pengeluaran');
        Route::get('/pengeluaran-get', [App\Http\Controllers\PengeluaranController::class, 'get'])->name('pengeluaran-get');
        Route::post('/pengeluaran-store', [App\Http\Controllers\PengeluaranController::class, 'store'])->name('pengeluaran-store');
        Route::get('/pengeluaran-edit/{params}', [App\Http\Controllers\PengeluaranController::class, 'edit'])->name('pengeluaran-edit');
        Route::post('/pengeluaran-update/{params}', [App\Http\Controllers\PengeluaranController::class, 'update'])->name('pengeluaran-update');
        Route::delete('/pengeluaran-delete/{params}', [App\Http\Controllers\PengeluaranController::class, 'delete'])->name('pengeluaran-delete');

        Route::get('/pemindahan', [App\Http\Controllers\PemindahanDanaController::class, 'index'])->name('pemindahan');
        Route::get('/pemindahan-get', [App\Http\Controllers\PemindahanDanaController::class, 'get'])->name('pemindahan-get');
        Route::post('/pemindahan-store', [App\Http\Controllers\PemindahanDanaController::class, 'store'])->name('pemindahan-store');
        Route::get('/pemindahan-edit/{params}', [App\Http\Controllers\PemindahanDanaController::class, 'edit'])->name('pemindahan-edit');
        Route::post('/pemindahan-update/{params}', [App\Http\Controllers\PemindahanDanaController::class, 'update'])->name('pemindahan-update');
        Route::delete('/pemindahan-delete/{params}', [App\Http\Controllers\PemindahanDanaController::class, 'delete'])->name('pemindahan-delete');

        Route::get('/vw-jurnal-umum', [App\Http\Controllers\ReportController::class, 'vw_jurnal_umum'])->name('vw-jurnal-umum');
        Route::get('/get-jurnal-umum', [App\Http\Controllers\ReportController::class, 'get_jurnal_umum'])->name('get-jurnal-umum');

        Route::get('/vw-buku-besar', [App\Http\Controllers\ReportController::class, 'vw_buku_besar'])->name('vw-buku-besar');
        Route::get('/get-buku-besar', [App\Http\Controllers\ReportController::class, 'get_buku_besar'])->name('get-buku-besar');

        Route::get('/vw-neraca', [App\Http\Controllers\ReportController::class, 'vw_neraca'])->name('vw-neraca');
        Route::get('/get-neraca', [App\Http\Controllers\ReportController::class, 'get_neraca'])->name('get-neraca');

        Route::get('/vw-laba-rugi', [App\Http\Controllers\ReportController::class, 'vw_laba_rugi'])->name('vw-laba-rugi');
        Route::get('/get-laba-rugi', [App\Http\Controllers\ReportController::class, 'get_laba_rugi'])->name('get-laba-rugi');

        Route::get('/vw-lap-transaksi', [App\Http\Controllers\LapTransakasi::class, 'index'])->name('vw-lap-transaksi');
        Route::get('/get-lap-transaksi', [App\Http\Controllers\LapTransakasi::class, 'get'])->name('get-lap-transaksi');
        // Route::get('/export-excel/{params?}', [App\Http\Controllers\LapTransakasi::class, 'export_excel'])->name('export-excel');
    });

    Route::get('sumary-report', [App\Http\Controllers\ClosingKasirController::class, 'sumaryreport'])->name('sumary-report');
});

Route::group([
    'prefix' => 'kasir',
    'middleware' => ['auth'],
    'as' => 'kasir.'
], function () {
    Route::get('/dashboard-kasir', [App\Http\Controllers\PenjualanController::class, 'index'])->name('dashboard-kasir');

    Route::get('/produk-get', [App\Http\Controllers\PenjualanController::class, 'get_produk'])->name('produk-get');
    Route::post('/penjualan-store', [App\Http\Controllers\PenjualanController::class, 'store'])->name('penjualan-store');

    Route::get('/print-struk', [App\Http\Controllers\PenjualanController::class, 'cetakStrukText'])->name('print-struk');
    // Route::get('/print-struk', [App\Http\Controllers\PenjualanController::class, 'cetakStrukText'])->name('print-struk');

    Route::get('/get-jasa', [App\Http\Controllers\PenjualanController::class, 'get_jasa'])->name('get-jasa');
    Route::get('/get-stock', [App\Http\Controllers\PenjualanController::class, 'get_stock'])->name('get-stock');

    Route::post('/closing', [App\Http\Controllers\ClosingKasirController::class, 'store'])->name('closing');

    Route::get('/get-penjualan', [App\Http\Controllers\PenjualanController::class, 'get_penjualan'])->name('get-penjualan');
    Route::get('/get-penjualan-detail/{params}', [App\Http\Controllers\PenjualanController::class, 'get_detail_penjualan'])->name('get-penjualan-detail');

    Route::get('/sumary-report/{params}', [App\Http\Controllers\ClosingKasirController::class, 'index'])->name('sumary-report');

    Route::get('/get-costumer-byPlat', [App\Http\Controllers\CostumerController::class, 'getCostumerByPlat'])->name('get-costumer-byPlat');
});

Route::get('/logout', [App\Http\Controllers\Auth::class, 'logout'])->name('logout');
