<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DaftarHadir\ZoomController;

Route::prefix('admin/daftar-hadir/zoom')
    ->name('admin.daftar-hadir.zoom.')
    ->group(function () {
        Route::get('/groups', [ZoomController::class, 'groups'])
            ->middleware('permission:admin.daftar-hadir.zoom.list')
            ->name('groups');

        Route::get('/', [ZoomController::class, 'groups'])
            ->middleware('permission:admin.daftar-hadir.zoom.list')
            ->name('index');

        Route::get('/detail', [ZoomController::class, 'detail'])
            ->middleware('permission:admin.daftar-hadir.zoom.detail')
            ->name('detail');

        Route::put('/{id}/hasil-test', [ZoomController::class, 'updateHasilTest'])
            ->middleware('permission:admin.daftar-hadir.zoom.update-hasil-test')
            ->whereUuid('id')
            ->name('update-hasil-test');

        Route::post('/{id}/hasil-test', [ZoomController::class, 'updateHasilTest'])
            ->middleware('permission:admin.daftar-hadir.zoom.update-hasil-test')
            ->whereUuid('id')
            ->name('upload-hasil-test');
    });