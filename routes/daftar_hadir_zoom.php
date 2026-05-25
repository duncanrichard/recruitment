<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DaftarHadir\ZoomController;

Route::prefix('admin/daftar-hadir/zoom')
    ->name('admin.daftar-hadir.zoom.')
    ->group(function () {
        Route::get('/groups', [ZoomController::class, 'groups'])->name('groups');
        Route::get('/detail', [ZoomController::class, 'detail'])->name('detail');
        Route::put('/{id}/hasil-test', [ZoomController::class, 'updateHasilTest'])->name('update-hasil-test');
    });