<?php

use App\Http\Controllers\CostumerController;
use App\Http\Controllers\ProdukController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/boot/kategori', [ProdukController::class, 'getKategori']);
Route::get('/boot/produk/{params}', [ProdukController::class, 'getProdukByKategori']);

Route::get('/boot/jasa', [App\Http\Controllers\JasaController::class, 'getJasaList']);

Route::get('/boot/costumer/{plat}', [CostumerController::class, 'getCostumerByPlatBoot']);
