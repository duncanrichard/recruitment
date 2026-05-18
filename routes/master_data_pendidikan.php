<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MasterData\PendidikanController;

Route::prefix('admin/master-data/pendidikan')->name('admin.master-data.pendidikan.')->group(function () {
    Route::get('/list', [PendidikanController::class, 'list'])->name('list');
    Route::post('/', [PendidikanController::class, 'store'])->name('store');
    Route::put('/{id}', [PendidikanController::class, 'update'])->name('update');
    Route::delete('/{id}', [PendidikanController::class, 'destroy'])->name('destroy');
});