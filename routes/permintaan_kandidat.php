<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PermintaanKandidatRecruitmentController;

Route::prefix('admin/permintaan-kandidat-recruitment')
    ->name('admin.permintaan-kandidat-recruitment.')
    ->controller(PermintaanKandidatRecruitmentController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/list', 'list')->name('list');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}', 'show')->name('show');
        Route::put('/{id}', 'update')->name('update');
        Route::patch('/{id}/status', 'updateStatus')->name('status');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });