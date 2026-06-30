<?php

use App\Http\Controllers\Admin\DaftarHadir\MmpiController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/daftar-hadir/mmpi')
    ->name('admin.daftar-hadir.mmpi.')
    ->controller(MmpiController::class)
    ->group(function () {
        /*
        |--------------------------------------------------------------------------
        | List / Group Daftar Hadir MMPI
        |--------------------------------------------------------------------------
        */
        Route::get('/groups', 'groups')
            ->middleware('permission:admin.daftar-hadir.mmpi.list')
            ->name('groups');

        Route::get('/list', 'list')
            ->middleware('permission:admin.daftar-hadir.mmpi.list')
            ->name('list');

        Route::get('/detail', 'detail')
            ->middleware('permission:admin.daftar-hadir.mmpi.detail')
            ->name('detail');

        /*
        |--------------------------------------------------------------------------
        | Update Kehadiran MMPI
        |--------------------------------------------------------------------------
        | Dipakai untuk dropdown:
        | - Hadir
        | - Tidak Hadir
        |
        | URL frontend:
        | PATCH /admin/daftar-hadir/mmpi/{jadwalTestMmpi}/kehadiran
        |--------------------------------------------------------------------------
        */
        Route::patch('/{jadwalTestMmpi}/kehadiran', 'updateKehadiran')
            ->middleware('permission:admin.daftar-hadir.mmpi.update-kehadiran')
            ->whereUuid('jadwalTestMmpi')
            ->name('kehadiran');

        /*
        |--------------------------------------------------------------------------
        | Update Hasil Test MMPI
        |--------------------------------------------------------------------------
        | Dipakai untuk simpan hasil:
        | - Lolos
        | - Tidak Lolos
        |
        | URL frontend:
        | PATCH /admin/daftar-hadir/mmpi/{jadwalTestMmpi}/hasil-test
        |--------------------------------------------------------------------------
        */
        Route::patch('/{jadwalTestMmpi}/hasil-test', 'updateHasilTest')
            ->middleware('permission:admin.daftar-hadir.mmpi.update-hasil-test')
            ->whereUuid('jadwalTestMmpi')
            ->name('hasil-test');

        /*
        |--------------------------------------------------------------------------
        | Upload File Hasil Test MMPI
        |--------------------------------------------------------------------------
        | Dipakai React ketika upload file memakai FormData:
        |
        | method POST
        | body:
        | - _method = PATCH
        | - hasil_test
        | - file_hasil_test_mmpi
        |--------------------------------------------------------------------------
        */
        Route::post('/{jadwalTestMmpi}/hasil-test', 'updateHasilTest')
            ->middleware('permission:admin.daftar-hadir.mmpi.update-hasil-test')
            ->whereUuid('jadwalTestMmpi')
            ->name('hasil-test.upload');
    });