<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MasterData\DataPerusahaanController;

Route::prefix('admin/master-data/perusahaan')->name('admin.master-data.perusahaan.')->group(function () {
    Route::get('/list', [DataPerusahaanController::class, 'list'])->name('list');
    Route::post('/', [DataPerusahaanController::class, 'store'])->name('store');
    Route::put('/{id}', [DataPerusahaanController::class, 'update'])->name('update');
    Route::delete('/{id}', [DataPerusahaanController::class, 'destroy'])->name('destroy');
});