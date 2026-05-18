<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MasterData\KewarganegaraanController;

Route::prefix('admin/master-data/kewarganegaraan')->name('admin.master-data.kewarganegaraan.')->group(function () {
    Route::get('/list', [KewarganegaraanController::class, 'list'])->name('list');
    Route::post('/', [KewarganegaraanController::class, 'store'])->name('store');
    Route::put('/{id}', [KewarganegaraanController::class, 'update'])->name('update');
    Route::delete('/{id}', [KewarganegaraanController::class, 'destroy'])->name('destroy');
});