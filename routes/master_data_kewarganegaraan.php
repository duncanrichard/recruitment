<?php

use App\Http\Controllers\Admin\MasterData\KewarganegaraanController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/master-data/kewarganegaraan')
    ->name('admin.master-data.kewarganegaraan.')
    ->group(function () {
        Route::get('/list', [KewarganegaraanController::class, 'list'])
            ->middleware('permission:admin.master-data.kewarganegaraan.list')
            ->name('list');

        Route::post('/', [KewarganegaraanController::class, 'store'])
            ->middleware('permission:admin.master-data.kewarganegaraan.store')
            ->name('store');

        Route::put('/{id}', [KewarganegaraanController::class, 'update'])
            ->middleware('permission:admin.master-data.kewarganegaraan.update')
            ->name('update');

        Route::delete('/{id}', [KewarganegaraanController::class, 'destroy'])
            ->middleware('permission:admin.master-data.kewarganegaraan.destroy')
            ->name('destroy');
    });
