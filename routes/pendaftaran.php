<?php

use App\Http\Controllers\Admin\DataPelamarController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman Pendaftaran Kandidat
|--------------------------------------------------------------------------
| /pendaftaran
| /pendaftaran/{token}
*/

Route::get('/pendaftaran', [DataPelamarController::class, 'pendaftaranIndex'])
    ->name('pendaftaran.index');

Route::get('/pendaftaran/{token}', [DataPelamarController::class, 'pendaftaranShow'])
    ->name('pendaftaran.show');

/*
|--------------------------------------------------------------------------
| API Pendaftaran Kandidat
|--------------------------------------------------------------------------
| Dipakai React halaman pendaftaran untuk mencari data berdasarkan token.
*/

Route::get('/pendaftaran/api/token/{token}', [DataPelamarController::class, 'findByToken'])
    ->name('pendaftaran.api.token');

/*
|--------------------------------------------------------------------------
| Admin Data Pelamar
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/data-pelamar', [DataPelamarController::class, 'index'])
        ->name('data-pelamar.index');

    Route::get('/data-pelamar/list', [DataPelamarController::class, 'list'])
        ->name('data-pelamar.list');

    Route::get('/data-pelamar/posisi/list', [DataPelamarController::class, 'posisiList'])
        ->name('data-pelamar.posisi.list');

    Route::get('/data-pelamar/perusahaan/list', [DataPelamarController::class, 'perusahaanList'])
        ->name('data-pelamar.perusahaan.list');

    Route::get('/data-pelamar/sumber-informasi/list', [DataPelamarController::class, 'sumberInformasiList'])
        ->name('data-pelamar.sumber-informasi.list');

    Route::get('/data-pelamar/pendidikan/list', [DataPelamarController::class, 'pendidikanList'])
        ->name('data-pelamar.pendidikan.list');

    Route::get('/data-pelamar/agama/list', [DataPelamarController::class, 'agamaList'])
        ->name('data-pelamar.agama.list');

    Route::get('/data-pelamar/kewarganegaraan/list', [DataPelamarController::class, 'kewarganegaraanList'])
        ->name('data-pelamar.kewarganegaraan.list');

    Route::get('/data-pelamar/status-pernikahan/list', [DataPelamarController::class, 'statusPernikahanList'])
        ->name('data-pelamar.status-pernikahan.list');

    Route::post('/data-pelamar', [DataPelamarController::class, 'store'])
        ->name('data-pelamar.store');

    Route::get('/data-pelamar/{id}', [DataPelamarController::class, 'show'])
        ->name('data-pelamar.show');

    Route::put('/data-pelamar/{id}', [DataPelamarController::class, 'update'])
        ->name('data-pelamar.update');

    Route::patch('/data-pelamar/{id}', [DataPelamarController::class, 'update'])
        ->name('data-pelamar.patch');

    Route::delete('/data-pelamar/{id}', [DataPelamarController::class, 'destroy'])
        ->name('data-pelamar.destroy');
});