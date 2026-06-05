<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\JadwalOfferingLetterController;

Route::prefix('admin/jadwal-ol')->group(function () {
    Route::get('/list', [JadwalOfferingLetterController::class, 'list'])
        ->name('admin.jadwal-ol.list');

    Route::get('/candidates', [JadwalOfferingLetterController::class, 'candidates'])
        ->name('admin.jadwal-ol.candidates');

    Route::post('/', [JadwalOfferingLetterController::class, 'store'])
        ->name('admin.jadwal-ol.store');

    Route::put('/{id}', [JadwalOfferingLetterController::class, 'update'])
        ->name('admin.jadwal-ol.update');

    Route::patch('/{id}/status', [JadwalOfferingLetterController::class, 'updateStatus'])
        ->name('admin.jadwal-ol.update-status');

    Route::delete('/{id}', [JadwalOfferingLetterController::class, 'destroy'])
        ->name('admin.jadwal-ol.destroy');
});