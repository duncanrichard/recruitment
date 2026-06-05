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

    /*
    |--------------------------------------------------------------------------
    | Kirim Pesan Fonnte
    |--------------------------------------------------------------------------
    | Route POST untuk tombol "Kirim Pesan WA" dari React.
    */
    Route::post('/data-pelamar/kirim-pesan-skrining', [DataPelamarController::class, 'kirimPesanSkrining'])
        ->name('data-pelamar.kirim-pesan-skrining');

    /*
    |--------------------------------------------------------------------------
    | Optional GET
    |--------------------------------------------------------------------------
    | Supaya kalau URL dibuka manual di browser tidak error 405.
    */
    Route::get('/data-pelamar/kirim-pesan-skrining', function () {
        return response()->json([
            'success' => false,
            'message' => 'Endpoint ini hanya untuk mengirim pesan melalui tombol Kirim Pesan WA. Gunakan method POST.',
        ], 405);
    })->name('data-pelamar.kirim-pesan-skrining.get');

    Route::post('/data-pelamar', [DataPelamarController::class, 'store'])
        ->name('data-pelamar.store');

    Route::get('/data-pelamar/{id}/detail', [DataPelamarController::class, 'detail'])
        ->whereUuid('id')
        ->name('data-pelamar.detail');

    Route::get('/data-pelamar/{id}/detail-data', [DataPelamarController::class, 'detailData'])
        ->whereUuid('id')
        ->name('data-pelamar.detail-data');

    Route::get('/data-pelamar/{id}', [DataPelamarController::class, 'show'])
        ->whereUuid('id')
        ->name('data-pelamar.show');

    Route::put('/data-pelamar/{id}', [DataPelamarController::class, 'update'])
        ->whereUuid('id')
        ->name('data-pelamar.update');

    Route::patch('/data-pelamar/{id}', [DataPelamarController::class, 'update'])
        ->whereUuid('id')
        ->name('data-pelamar.patch');

    Route::delete('/data-pelamar/{id}', [DataPelamarController::class, 'destroy'])
        ->whereUuid('id')
        ->name('data-pelamar.destroy');
});