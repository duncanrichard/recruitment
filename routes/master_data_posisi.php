<?php

use App\Http\Controllers\Admin\MasterData\PosisiController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/master-data/posisi')
    ->name('admin.master-data.posisi.')
    ->middleware('auth')
    ->group(function () {
        Route::get('/', [PosisiController::class, 'index'])
            ->name('index');

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
