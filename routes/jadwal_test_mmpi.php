<?php

use App\Http\Controllers\Admin\JadwalTest\MmpiController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/jadwal-test/mmpi')
    ->name('admin.jadwal-test.mmpi.')
    ->controller(MmpiController::class)
    ->group(function () {
        Route::get('/', 'index')
            ->middleware('permission:admin.jadwal-test.mmpi.list')
            ->name('index');

        Route::get('/list', 'list')
            ->middleware('permission:admin.jadwal-test.mmpi.list')
            ->name('list');

        Route::get('/kandidat-lolos-zoom', 'kandidatLolosZoom')
            ->middleware('permission:admin.jadwal-test.mmpi.options')
            ->name('kandidat-lolos-zoom');

        Route::post('/', 'store')
            ->middleware('permission:admin.jadwal-test.mmpi.store')
            ->name('store');

        Route::delete('/{id}', 'destroy')
            ->middleware('permission:admin.jadwal-test.mmpi.destroy')
            ->whereUuid('id')
            ->name('destroy');
    });
