<?php

use App\Http\Controllers\PendaftaranController;
use Illuminate\Support\Facades\Route;

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
|--------------------------------------------------------------------------
| API Tahapan Seleksi
|--------------------------------------------------------------------------
*/
Route::get('/pendaftaran/api/token/{token}/tahapan', [PendaftaranController::class, 'tahapanByToken'])
    ->name('pendaftaran.api.token.tahapan');

Route::get('/pendaftaran/api/token/{token}/cek-tahapan', [PendaftaranController::class, 'cekTahapanByToken'])
    ->name('pendaftaran.api.token.cek-tahapan');

/*
|--------------------------------------------------------------------------
| API Kehadiran Jadwal Test
|--------------------------------------------------------------------------
| WAJIB di atas /pendaftaran/{token}
|--------------------------------------------------------------------------
*/
Route::patch('/pendaftaran/api/token/{token}/jadwal-test/{jadwalTest}/kehadiran', [PendaftaranController::class, 'updateKehadiranJadwalTest'])
    ->name('pendaftaran.api.token.jadwal-test.kehadiran');

/*
|--------------------------------------------------------------------------
| Step 1 - Data Diri
|--------------------------------------------------------------------------
*/
Route::patch('/pendaftaran/api/token/{token}/data-diri', [PendaftaranController::class, 'updateDataDiriByToken'])
    ->name('pendaftaran.api.token.data-diri.update');

Route::get('/pendaftaran/api/token/{token}/data-diri', function (string $token) {
    return redirect()->route('pendaftaran.show', ['token' => $token]);
})->name('pendaftaran.api.token.data-diri.show');

/*
|--------------------------------------------------------------------------
| Step 2 - Riwayat Keluarga
|--------------------------------------------------------------------------
*/
Route::patch('/pendaftaran/api/token/{token}/riwayat-keluarga', [PendaftaranController::class, 'updateRiwayatKeluargaByToken'])
    ->name('pendaftaran.api.token.riwayat-keluarga.update');

Route::get('/pendaftaran/api/token/{token}/riwayat-keluarga', function (string $token) {
    return redirect()->route('pendaftaran.show', ['token' => $token]);
})->name('pendaftaran.api.token.riwayat-keluarga.show');

/*
|--------------------------------------------------------------------------
| Step 3 - Riwayat Kesehatan
|--------------------------------------------------------------------------
*/
Route::patch('/pendaftaran/api/token/{token}/riwayat-kesehatan', [PendaftaranController::class, 'updateRiwayatKesehatanByToken'])
    ->name('pendaftaran.api.token.riwayat-kesehatan.update');

Route::get('/pendaftaran/api/token/{token}/riwayat-kesehatan', function (string $token) {
    return redirect()->route('pendaftaran.show', ['token' => $token]);
})->name('pendaftaran.api.token.riwayat-kesehatan.show');

/*
|--------------------------------------------------------------------------
| Step 4 - Riwayat Pekerjaan
|--------------------------------------------------------------------------
*/
Route::patch('/pendaftaran/api/token/{token}/riwayat-pekerjaan', [PendaftaranController::class, 'updateRiwayatPekerjaanByToken'])
    ->name('pendaftaran.api.token.riwayat-pekerjaan.update');

Route::get('/pendaftaran/api/token/{token}/riwayat-pekerjaan', function (string $token) {
    return redirect()->route('pendaftaran.show', ['token' => $token]);
})->name('pendaftaran.api.token.riwayat-pekerjaan.show');

/*
|--------------------------------------------------------------------------
| Step 5 - Kesiapan Bekerja
|--------------------------------------------------------------------------
*/
Route::patch('/pendaftaran/api/token/{token}/kesiapan-bekerja', [PendaftaranController::class, 'updateKesiapanBekerjaByToken'])
    ->name('pendaftaran.api.token.kesiapan-bekerja.update');

Route::get('/pendaftaran/api/token/{token}/kesiapan-bekerja', function (string $token) {
    return redirect()->route('pendaftaran.show', ['token' => $token]);
})->name('pendaftaran.api.token.kesiapan-bekerja.show');

/*
|--------------------------------------------------------------------------
| Halaman Pendaftaran Kandidat
|--------------------------------------------------------------------------
*/
Route::get('/pendaftaran', [PendaftaranController::class, 'index'])
    ->name('pendaftaran.index');

/*
|--------------------------------------------------------------------------
| Halaman Cek Tahapan
|--------------------------------------------------------------------------
*/
Route::get('/pendaftaran/{token}/cek-tahapan', [PendaftaranController::class, 'cekTahapanByToken'])
    ->name('pendaftaran.cek-tahapan');

Route::get('/pendaftaran/{token}/tahapan', [PendaftaranController::class, 'tahapanByToken'])
    ->name('pendaftaran.tahapan');

/*
|--------------------------------------------------------------------------
| Route Token Pendaftaran
|--------------------------------------------------------------------------
| Route ini wajib paling bawah.
|--------------------------------------------------------------------------
*/
Route::get('/pendaftaran/{token}', [PendaftaranController::class, 'show'])
    ->name('pendaftaran.show');