<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MasterData\DivisiController;

Route::prefix('admin/master-data/divisi')
    ->name('admin.master-data.divisi.')
    ->middleware('auth')
    ->group(function () {
        Route::get('/list', [DivisiController::class, 'list'])
            ->middleware('permission:admin.master-data.divisi.list')
            ->name('list');

        Route::post('/', [DivisiController::class, 'store'])
            ->middleware('permission:admin.master-data.divisi.store')
            ->name('store');

        Route::put('/{id}', [DivisiController::class, 'update'])
            ->middleware('permission:admin.master-data.divisi.update')
            ->name('update');

        Route::delete('/{id}', [DivisiController::class, 'destroy'])
            ->middleware('permission:admin.master-data.divisi.destroy')
            ->name('destroy');
    });