<?php

use App\Http\Controllers\Admin\DaftarHadir\MmpiController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/daftar-hadir/mmpi')
    ->name('admin.daftar-hadir.mmpi.')
    ->group(function () {
        Route::get('/groups', [MmpiController::class, 'groups'])
            ->middleware('permission:admin.daftar-hadir.mmpi.list')
            ->name('groups');

        Route::get('/list', [MmpiController::class, 'list'])
            ->middleware('permission:admin.daftar-hadir.mmpi.list')
            ->name('list');

        Route::get('/detail', [MmpiController::class, 'detail'])
            ->middleware('permission:admin.daftar-hadir.mmpi.detail')
            ->name('detail');

        Route::patch('/{jadwalTestMmpi}/kehadiran', [MmpiController::class, 'updateKehadiran'])
            ->middleware('permission:admin.daftar-hadir.mmpi.update-kehadiran')
            ->whereUuid('jadwalTestMmpi')
            ->name('kehadiran');

        Route::patch('/{jadwalTestMmpi}/hasil-test', [MmpiController::class, 'updateHasilTest'])
            ->middleware('permission:admin.daftar-hadir.mmpi.update-hasil-test')
            ->whereUuid('jadwalTestMmpi')
            ->name('hasil-test');

        /*
        |--------------------------------------------------------------------------
        | Upload file hasil test MMPI
        |--------------------------------------------------------------------------
        | Dipakai React saat upload file karena FormData dikirim dengan POST + _method=PATCH.
        | Route PATCH di atas tetap aman untuk request lama yang masih JSON.
        |--------------------------------------------------------------------------
        */
        Route::post('/{jadwalTestMmpi}/hasil-test', [MmpiController::class, 'updateHasilTest'])
            ->middleware('permission:admin.daftar-hadir.mmpi.update-hasil-test')
            ->whereUuid('jadwalTestMmpi')
            ->name('hasil-test.upload');
    });