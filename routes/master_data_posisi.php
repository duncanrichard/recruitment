<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MasterData\PosisiController;

Route::prefix('admin/master-data/posisi')
    ->name('admin.master-data.posisi.')
    ->middleware('auth')
    ->group(function () {
        Route::get('/list', [PosisiController::class, 'list'])
            ->middleware('permission:admin.master-data.posisi.list')
            ->name('list');

        Route::post('/', [PosisiController::class, 'store'])
            ->middleware('permission:admin.master-data.posisi.store')
            ->name('store');

        Route::put('/{id}', [PosisiController::class, 'update'])
            ->middleware('permission:admin.master-data.posisi.update')
            ->name('update');

        Route::delete('/{id}', [PosisiController::class, 'destroy'])
            ->middleware('permission:admin.master-data.posisi.destroy')
            ->name('destroy');
    });