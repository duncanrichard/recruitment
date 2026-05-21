<?php

use App\Http\Controllers\PendaftaranController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Pendaftaran Kandidat
|--------------------------------------------------------------------------
| Semua route API wajib diletakkan di atas /pendaftaran/{token}
| agar tidak terbaca sebagai token pendaftaran.
*/

/*
|--------------------------------------------------------------------------
| Master Data
|--------------------------------------------------------------------------
*/
Route::get('/pendaftaran/api/master/pendaftaran', [PendaftaranController::class, 'masterPendaftaran'])
    ->name('pendaftaran.api.master.pendaftaran');

Route::get('/pendaftaran/api/master/pendidikan', [PendaftaranController::class, 'masterPendidikan'])
    ->name('pendaftaran.api.master.pendidikan');

Route::get('/pendaftaran/api/master/status-pernikahan', [PendaftaranController::class, 'masterStatusPernikahan'])
    ->name('pendaftaran.api.master.status-pernikahan');

/*
|--------------------------------------------------------------------------
| API Wilayah
|--------------------------------------------------------------------------
*/
Route::get('/pendaftaran/api/wilayah/provinces', [PendaftaranController::class, 'wilayahProvinces'])
    ->name('pendaftaran.api.wilayah.provinces');

Route::get('/pendaftaran/api/wilayah/regencies/{province_code}', [PendaftaranController::class, 'wilayahRegencies'])
    ->name('pendaftaran.api.wilayah.regencies');

Route::get('/pendaftaran/api/wilayah/districts/{regency_code}', [PendaftaranController::class, 'wilayahDistricts'])
    ->name('pendaftaran.api.wilayah.districts');

Route::get('/pendaftaran/api/wilayah/villages/{district_code}', [PendaftaranController::class, 'wilayahVillages'])
    ->name('pendaftaran.api.wilayah.villages');

/*
|--------------------------------------------------------------------------
| API Token Pelamar
|--------------------------------------------------------------------------
*/
Route::get('/pendaftaran/api/token/{token}', [PendaftaranController::class, 'findByToken'])
    ->name('pendaftaran.api.token');

/*
| Step 1 - Data Diri
*/
Route::patch('/pendaftaran/api/token/{token}/data-diri', [PendaftaranController::class, 'updateDataDiriByToken'])
    ->name('pendaftaran.api.token.data-diri.update');

Route::get('/pendaftaran/api/token/{token}/data-diri', function (string $token) {
    return redirect()->route('pendaftaran.show', ['token' => $token]);
})->name('pendaftaran.api.token.data-diri.show');

/*
| Step 2 - Riwayat Keluarga
*/
Route::patch('/pendaftaran/api/token/{token}/riwayat-keluarga', [PendaftaranController::class, 'updateRiwayatKeluargaByToken'])
    ->name('pendaftaran.api.token.riwayat-keluarga.update');

Route::get('/pendaftaran/api/token/{token}/riwayat-keluarga', function (string $token) {
    return redirect()->route('pendaftaran.show', ['token' => $token]);
})->name('pendaftaran.api.token.riwayat-keluarga.show');

/*
| Step 3 - Riwayat Kesehatan
*/
Route::patch('/pendaftaran/api/token/{token}/riwayat-kesehatan', [PendaftaranController::class, 'updateRiwayatKesehatanByToken'])
    ->name('pendaftaran.api.token.riwayat-kesehatan.update');

Route::get('/pendaftaran/api/token/{token}/riwayat-kesehatan', function (string $token) {
    return redirect()->route('pendaftaran.show', ['token' => $token]);
})->name('pendaftaran.api.token.riwayat-kesehatan.show');

/*
|--------------------------------------------------------------------------
| Halaman Pendaftaran Kandidat
|--------------------------------------------------------------------------
*/
Route::get('/pendaftaran', [PendaftaranController::class, 'index'])
    ->name('pendaftaran.index');

/*
| Route token wajib paling bawah agar route seperti:
| /pendaftaran/api/master/pendaftaran
| /pendaftaran/api/token/{token}/riwayat-keluarga
| /pendaftaran/api/token/{token}/riwayat-kesehatan
| /pendaftaran/api/wilayah/provinces
| tidak tertangkap sebagai token.
*/
Route::get('/pendaftaran/{token}', [PendaftaranController::class, 'show'])
    ->name('pendaftaran.show');