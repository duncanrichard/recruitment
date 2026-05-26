<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MasterData\JabatanController;

Route::prefix('admin/master-data/jabatan')
    ->name('admin.master-data.jabatan.')
    ->group(function () {
        Route::get('/list', [JabatanController::class, 'list'])->name('list');
        Route::post('/', [JabatanController::class, 'store'])->name('store');
        Route::put('/{id}', [JabatanController::class, 'update'])->name('update');
        Route::delete('/{id}', [JabatanController::class, 'destroy'])->name('destroy');
    });