<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MasterData\JabatanController;

Route::prefix('admin/master-data/jabatan')
    ->name('admin.master-data.jabatan.')
    ->group(function () {
        Route::get('/list', [JabatanController::class, 'list'])
            ->middleware('permission:admin.master-data.jabatan.list')
            ->name('list');

        Route::post('/', [JabatanController::class, 'store'])
            ->middleware('permission:admin.master-data.jabatan.store')
            ->name('store');

        Route::put('/{id}', [JabatanController::class, 'update'])
            ->middleware('permission:admin.master-data.jabatan.update')
            ->name('update');

        Route::delete('/{id}', [JabatanController::class, 'destroy'])
            ->middleware('permission:admin.master-data.jabatan.destroy')
            ->name('destroy');
    });