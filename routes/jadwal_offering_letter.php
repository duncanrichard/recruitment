<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\JadwalOfferingLetterController;

Route::prefix('admin/jadwal-ol')
    ->name('admin.jadwal-ol.')
    ->group(function () {
        Route::get('/list', [JadwalOfferingLetterController::class, 'list'])
            ->middleware('permission:admin.jadwal-ol.list')
            ->name('list');

        Route::get('/candidates', [JadwalOfferingLetterController::class, 'candidates'])
            ->middleware('permission:admin.jadwal-ol.candidates')
            ->name('candidates');

        Route::post('/', [JadwalOfferingLetterController::class, 'store'])
            ->middleware('permission:admin.jadwal-ol.store')
            ->name('store');

        Route::put('/{id}', [JadwalOfferingLetterController::class, 'update'])
            ->middleware('permission:admin.jadwal-ol.update')
            ->whereUuid('id')
            ->name('update');

        Route::patch('/{id}/status', [JadwalOfferingLetterController::class, 'updateStatus'])
            ->middleware('permission:admin.jadwal-ol.update-status')
            ->whereUuid('id')
            ->name('update-status');

        Route::delete('/{id}', [JadwalOfferingLetterController::class, 'destroy'])
            ->middleware('permission:admin.jadwal-ol.destroy')
            ->whereUuid('id')
            ->name('destroy');
    });