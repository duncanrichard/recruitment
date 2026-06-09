<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MasterData\OpsiKacamataController;

Route::prefix('admin/master-data/opsi-kacamata')
    ->name('admin.master-data.opsi-kacamata.')
    ->group(function () {
        Route::get('/list', [OpsiKacamataController::class, 'list'])
            ->middleware('permission:admin.master-data.opsi-kacamata.list')
            ->name('list');

        Route::post('/', [OpsiKacamataController::class, 'store'])
            ->middleware('permission:admin.master-data.opsi-kacamata.store')
            ->name('store');

        Route::put('/{id}', [OpsiKacamataController::class, 'update'])
            ->middleware('permission:admin.master-data.opsi-kacamata.update')
            ->name('update');

        Route::delete('/{id}', [OpsiKacamataController::class, 'destroy'])
            ->middleware('permission:admin.master-data.opsi-kacamata.destroy')
            ->name('destroy');
    });