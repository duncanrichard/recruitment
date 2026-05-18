<?php

use App\Http\Controllers\Admin\DataPelamarController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/data-pelamar', [DataPelamarController::class, 'index'])
        ->name('data-pelamar.index');

    Route::get('/data-pelamar/list', [DataPelamarController::class, 'list'])
        ->name('data-pelamar.list');

    Route::get('/data-pelamar/posisi/list', [DataPelamarController::class, 'posisiList'])
        ->name('data-pelamar.posisi.list');

    Route::get('/data-pelamar/perusahaan/list', [DataPelamarController::class, 'perusahaanList'])
        ->name('data-pelamar.perusahaan.list');

    Route::get('/data-pelamar/pendidikan/list', [DataPelamarController::class, 'pendidikanList'])
        ->name('data-pelamar.pendidikan.list');

    Route::get('/data-pelamar/agama/list', [DataPelamarController::class, 'agamaList'])
        ->name('data-pelamar.agama.list');

    Route::get('/data-pelamar/kewarganegaraan/list', [DataPelamarController::class, 'kewarganegaraanList'])
        ->name('data-pelamar.kewarganegaraan.list');

    Route::get('/data-pelamar/status-pernikahan/list', [DataPelamarController::class, 'statusPernikahanList'])
        ->name('data-pelamar.status-pernikahan.list');

    Route::get('/data-pelamar/sumber-informasi/list', [DataPelamarController::class, 'sumberInformasiList'])
        ->name('data-pelamar.sumber-informasi.list');

    Route::post('/data-pelamar', [DataPelamarController::class, 'store'])
        ->name('data-pelamar.store');

    Route::delete('/data-pelamar/{id}', [DataPelamarController::class, 'destroy'])
        ->name('data-pelamar.destroy');
});