<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MasterData\AgamaController;

Route::prefix('admin/master-data/agama')
    ->name('admin.master-data.agama.')
    ->group(function () {
        Route::get('/list', [AgamaController::class, 'list'])
            ->middleware('permission:admin.master-data.agama.list')
            ->name('list');

        Route::post('/', [AgamaController::class, 'store'])
            ->middleware('permission:admin.master-data.agama.store')
            ->name('store');

        Route::put('/{id}', [AgamaController::class, 'update'])
            ->middleware('permission:admin.master-data.agama.update')
            ->name('update');

        Route::delete('/{id}', [AgamaController::class, 'destroy'])
            ->middleware('permission:admin.master-data.agama.destroy')
            ->name('destroy');
    });