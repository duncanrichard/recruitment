<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MasterData\DivisiController;

Route::prefix('admin/master-data/divisi')
    ->name('admin.master-data.divisi.')
    ->group(function () {
        Route::get('/list', [DivisiController::class, 'list'])->name('list');
        Route::post('/', [DivisiController::class, 'store'])->name('store');
        Route::put('/{id}', [DivisiController::class, 'update'])->name('update');
        Route::delete('/{id}', [DivisiController::class, 'destroy'])->name('destroy');
    });