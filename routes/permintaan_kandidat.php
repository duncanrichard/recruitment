<?php

use App\Http\Controllers\Admin\PermintaanKandidatRecruitmentController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/permintaan-kandidat-recruitment')
    ->name('admin.permintaan-kandidat-recruitment.')
    ->controller(PermintaanKandidatRecruitmentController::class)
    ->group(function () {
        Route::get('/', 'index')
            ->middleware('permission:admin.permintaan-kandidat-recruitment.list')
            ->name('index');

        Route::get('/list', 'list')
            ->middleware('permission:admin.permintaan-kandidat-recruitment.list')
            ->name('list');

        Route::post('/', 'store')
            ->middleware('permission:admin.permintaan-kandidat-recruitment.store')
            ->name('store');

        Route::get('/{id}', 'show')
            ->middleware('permission:admin.permintaan-kandidat-recruitment.show')
            ->whereUuid('id')
            ->name('show');

        Route::put('/{id}', 'update')
            ->middleware('permission:admin.permintaan-kandidat-recruitment.update')
            ->whereUuid('id')
            ->name('update');

        Route::patch('/{id}/status', 'updateStatus')
            ->middleware('permission:admin.permintaan-kandidat-recruitment.status')
            ->whereUuid('id')
            ->name('status');

        Route::delete('/{id}', 'destroy')
            ->middleware('permission:admin.permintaan-kandidat-recruitment.destroy')
            ->whereUuid('id')
            ->name('destroy');
    });
