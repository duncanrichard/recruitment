<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MasterData\OpsiKacamataController;

Route::prefix('admin/master-data/opsi-kacamata')->name('admin.master-data.opsi-kacamata.')->group(function () {
    Route::get('/list', [OpsiKacamataController::class, 'list'])->name('list');
    Route::post('/', [OpsiKacamataController::class, 'store'])->name('store');
    Route::put('/{id}', [OpsiKacamataController::class, 'update'])->name('update');
    Route::delete('/{id}', [OpsiKacamataController::class, 'destroy'])->name('destroy');
});