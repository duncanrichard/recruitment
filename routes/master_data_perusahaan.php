<?php

use App\Http\Controllers\Admin\MasterData\DataPerusahaanController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/master-data/perusahaan')
    ->name('admin.master-data.perusahaan.')
    ->group(function () {
        Route::get('/list', [DataPerusahaanController::class, 'list'])
            ->middleware('permission:admin.master-data.perusahaan.list')
            ->name('list');

        Route::post('/', [DataPerusahaanController::class, 'store'])
            ->middleware('permission:admin.master-data.perusahaan.store')
            ->name('store');

        Route::put('/{id}', [DataPerusahaanController::class, 'update'])
            ->middleware('permission:admin.master-data.perusahaan.update')
            ->name('update');

        Route::delete('/{id}', [DataPerusahaanController::class, 'destroy'])
            ->middleware('permission:admin.master-data.perusahaan.destroy')
            ->name('destroy');

        Route::post('/{id}/validasi-fonnte', [DataPerusahaanController::class, 'validasiFonnte'])
            ->middleware('permission:admin.master-data.perusahaan.update')
            ->name('validasi-fonnte');
    });