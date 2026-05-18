<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MasterData\PosisiController;

Route::prefix('admin/master-data/posisi')
    ->name('admin.master-data.posisi.')
    ->group(function () {
        Route::get('/list', [PosisiController::class, 'list'])->name('list');
        Route::post('/', [PosisiController::class, 'store'])->name('store');
        Route::put('/{id}', [PosisiController::class, 'update'])->name('update');
        Route::delete('/{id}', [PosisiController::class, 'destroy'])->name('destroy');
    });