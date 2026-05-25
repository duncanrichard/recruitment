<?php

use App\Http\Controllers\Admin\DaftarHadir\MmpiController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/daftar-hadir/mmpi')
    ->name('admin.daftar-hadir.mmpi.')
    ->group(function () {
        Route::get('/groups', [MmpiController::class, 'groups'])->name('groups');
        Route::get('/list', [MmpiController::class, 'list'])->name('list');
        Route::get('/detail', [MmpiController::class, 'detail'])->name('detail');
        Route::patch('/{jadwalTestMmpi}/kehadiran', [MmpiController::class, 'updateKehadiran'])->name('kehadiran');
        Route::patch('/{jadwalTestMmpi}/hasil-test', [MmpiController::class, 'updateHasilTest'])->name('hasil-test');
    });
