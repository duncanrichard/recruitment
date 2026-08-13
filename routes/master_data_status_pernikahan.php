<?php

use App\Http\Controllers\Admin\MasterData\StatusPernikahanController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/master-data/status-pernikahan')
    ->name('admin.master-data.status-pernikahan.')
    ->group(function () {
        Route::get('/list', [StatusPernikahanController::class, 'list'])
            ->middleware('permission:admin.master-data.status-pernikahan.list')
            ->name('list');

        Route::post('/', [StatusPernikahanController::class, 'store'])
            ->middleware('permission:admin.master-data.status-pernikahan.store')
            ->name('store');

        Route::put('/{id}', [StatusPernikahanController::class, 'update'])
            ->middleware('permission:admin.master-data.status-pernikahan.update')
            ->name('update');

        Route::delete('/{id}', [StatusPernikahanController::class, 'destroy'])
            ->middleware('permission:admin.master-data.status-pernikahan.destroy')
            ->name('destroy');
    });
