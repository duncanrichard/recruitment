<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MasterData\AgamaController;

Route::prefix('admin/master-data/agama')->name('admin.master-data.agama.')->group(function () {
    Route::get('/list', [AgamaController::class, 'list'])->name('list');
    Route::post('/', [AgamaController::class, 'store'])->name('store');
    Route::put('/{id}', [AgamaController::class, 'update'])->name('update');
    Route::delete('/{id}', [AgamaController::class, 'destroy'])->name('destroy');
});