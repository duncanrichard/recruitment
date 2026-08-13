<?php

use App\Http\Controllers\Admin\MasterData\SumberInformasiController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/master-data/sumber-informasi')
    ->name('admin.master-data.sumber-informasi.')
    ->group(function () {
        Route::get('/list', [SumberInformasiController::class, 'list'])
            ->middleware('permission:admin.master-data.sumber-informasi.list')
            ->name('list');

        Route::post('/', [SumberInformasiController::class, 'store'])
            ->middleware('permission:admin.master-data.sumber-informasi.store')
            ->name('store');

        Route::put('/{id}', [SumberInformasiController::class, 'update'])
            ->middleware('permission:admin.master-data.sumber-informasi.update')
            ->name('update');

        Route::delete('/{id}', [SumberInformasiController::class, 'destroy'])
            ->middleware('permission:admin.master-data.sumber-informasi.destroy')
            ->name('destroy');
    });
