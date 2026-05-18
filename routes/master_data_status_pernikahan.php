<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MasterData\StatusPernikahanController;

Route::prefix('admin/master-data/status-pernikahan')->name('admin.master-data.status-pernikahan.')->group(function () {
    Route::get('/list', [StatusPernikahanController::class, 'list'])->name('list');
    Route::post('/', [StatusPernikahanController::class, 'store'])->name('store');
    Route::put('/{id}', [StatusPernikahanController::class, 'update'])->name('update');
    Route::delete('/{id}', [StatusPernikahanController::class, 'destroy'])->name('destroy');
});