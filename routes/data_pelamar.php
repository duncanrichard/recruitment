<?php

use App\Http\Controllers\Admin\CandidateDocumentController;
use App\Http\Controllers\Admin\DataPelamarController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/data-pelamar', [DataPelamarController::class, 'index'])
            ->middleware('permission:admin.data-pelamar.list')
            ->name('data-pelamar.index');

        Route::get('/data-pelamar/list', [DataPelamarController::class, 'list'])
            ->middleware('permission:admin.data-pelamar.list')
            ->name('data-pelamar.list');

        /*
        |--------------------------------------------------------------------------
        | Options / Referensi Form Data Pelamar
        |--------------------------------------------------------------------------
        */
        Route::get('/data-pelamar/posisi/list', [DataPelamarController::class, 'posisiList'])
            ->middleware('permission:admin.data-pelamar.list')
            ->name('data-pelamar.posisi.list');

        Route::post('/data-pelamar/posisi/options', [DataPelamarController::class, 'storePosisiOption'])
            ->middleware('permission:admin.master-data.posisi.store')
            ->name('data-pelamar.posisi.options.store');

        Route::get('/data-pelamar/perusahaan/list', [DataPelamarController::class, 'perusahaanList'])
            ->middleware('permission:admin.data-pelamar.list')
            ->name('data-pelamar.perusahaan.list');

        Route::post('/data-pelamar/perusahaan/options', [DataPelamarController::class, 'storePerusahaanOption'])
            ->middleware('permission:admin.master-data.perusahaan.store')
            ->name('data-pelamar.perusahaan.options.store');

        foreach (['sumber-informasi', 'pendidikan', 'agama', 'kewarganegaraan', 'status-pernikahan'] as $optionType) {
            Route::post("/data-pelamar/{$optionType}/options", [DataPelamarController::class, 'storeSimpleOption'])
                ->defaults('type', $optionType)
                ->middleware("permission:admin.master-data.{$optionType}.store")
                ->name("data-pelamar.{$optionType}.options.store");
        }

        Route::get('/data-pelamar/pendidikan/list', [DataPelamarController::class, 'pendidikanList'])
            ->middleware('permission:admin.data-pelamar.list')
            ->name('data-pelamar.pendidikan.list');

        Route::get('/data-pelamar/agama/list', [DataPelamarController::class, 'agamaList'])
            ->middleware('permission:admin.data-pelamar.list')
            ->name('data-pelamar.agama.list');

        Route::get('/data-pelamar/kewarganegaraan/list', [DataPelamarController::class, 'kewarganegaraanList'])
            ->middleware('permission:admin.data-pelamar.list')
            ->name('data-pelamar.kewarganegaraan.list');

        Route::get('/data-pelamar/status-pernikahan/list', [DataPelamarController::class, 'statusPernikahanList'])
            ->middleware('permission:admin.data-pelamar.list')
            ->name('data-pelamar.status-pernikahan.list');

        Route::get('/data-pelamar/sumber-informasi/list', [DataPelamarController::class, 'sumberInformasiList'])
            ->middleware('permission:admin.data-pelamar.list')
            ->name('data-pelamar.sumber-informasi.list');

        /*
        |--------------------------------------------------------------------------
        | Kirim Pesan Fonnte
        |--------------------------------------------------------------------------
        */
        Route::post('/data-pelamar/kirim-pesan-skrining', [DataPelamarController::class, 'kirimPesanSkrining'])
            ->middleware('permission:admin.data-pelamar.send-message')
            ->name('data-pelamar.kirim-pesan-skrining');

        Route::get('/data-pelamar/kirim-pesan-skrining', function () {
            return response()->json([
                'success' => false,
                'message' => 'Endpoint ini hanya untuk mengirim pesan melalui tombol Kirim Pesan WA. Gunakan method POST.',
            ], 405);
        })
            ->middleware('permission:admin.data-pelamar.send-message')
            ->name('data-pelamar.kirim-pesan-skrining.get');

        /*
        |--------------------------------------------------------------------------
        | CRUD Data Pelamar
        |--------------------------------------------------------------------------
        */
        Route::post('/data-pelamar', [DataPelamarController::class, 'store'])
            ->middleware('permission:admin.data-pelamar.store')
            ->name('data-pelamar.store');

        Route::get('/data-pelamar/{id}/detail', [DataPelamarController::class, 'detail'])
            ->middleware('permission:admin.data-pelamar.detail')
            ->whereUuid('id')
            ->name('data-pelamar.detail');

        Route::get('/data-pelamar/{candidate}/documents/{type}', [CandidateDocumentController::class, 'show'])
            ->middleware('permission:admin.data-pelamar.download-document')
            ->whereUuid('candidate')
            ->whereIn('type', ['cv', 'foto'])
            ->name('data-pelamar.documents.show');

        Route::get('/data-pelamar/{id}/detail-data', [DataPelamarController::class, 'detailData'])
            ->middleware('permission:admin.data-pelamar.detail')
            ->whereUuid('id')
            ->name('data-pelamar.detail-data');

        Route::get('/data-pelamar/{id}', [DataPelamarController::class, 'show'])
            ->middleware('permission:admin.data-pelamar.detail')
            ->whereUuid('id')
            ->name('data-pelamar.show');

        Route::put('/data-pelamar/{id}', [DataPelamarController::class, 'update'])
            ->middleware('permission:admin.data-pelamar.update')
            ->whereUuid('id')
            ->name('data-pelamar.update');

        Route::patch('/data-pelamar/{id}', [DataPelamarController::class, 'update'])
            ->middleware('permission:admin.data-pelamar.update')
            ->whereUuid('id')
            ->name('data-pelamar.patch');

        Route::delete('/data-pelamar/{id}', [DataPelamarController::class, 'destroy'])
            ->middleware('permission:admin.data-pelamar.destroy')
            ->whereUuid('id')
            ->name('data-pelamar.destroy');
    });
