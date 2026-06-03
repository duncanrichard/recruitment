<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MasterData\PendidikanController;

Route::prefix('admin/master-data/pendidikan')
    ->name('admin.master-data.pendidikan.')
    ->middleware('auth')
    ->group(function () {
        Route::get('/list', [PendidikanController::class, 'list'])
            ->middleware('permission:admin.master-data.pendidikan.list')
            ->name('list');

        Route::post('/', [PendidikanController::class, 'store'])
            ->middleware('permission:admin.master-data.pendidikan.store')
            ->name('store');

        Route::put('/{id}', [PendidikanController::class, 'update'])
            ->middleware('permission:admin.master-data.pendidikan.update')
            ->name('update');

        Route::delete('/{id}', [PendidikanController::class, 'destroy'])
            ->middleware('permission:admin.master-data.pendidikan.destroy')
            ->name('destroy');
    });