<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MasterData\SumberInformasiController;

Route::prefix('admin/master-data/sumber-informasi')->name('admin.master-data.sumber-informasi.')->group(function () {
    Route::get('/list', [SumberInformasiController::class, 'list'])->name('list');
    Route::post('/', [SumberInformasiController::class, 'store'])->name('store');
    Route::put('/{id}', [SumberInformasiController::class, 'update'])->name('update');
    Route::delete('/{id}', [SumberInformasiController::class, 'destroy'])->name('destroy');
});