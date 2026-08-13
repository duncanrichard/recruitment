<?php

use App\Http\Controllers\Admin\JadwalTestZoomController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->name('admin.')
    ->group(function () {
        /*
        |--------------------------------------------------------------------------
        | Jadwal Test Zoom
        |--------------------------------------------------------------------------
        | Urutan route penting:
        | - route statis harus di atas route dinamis {id}
        | - supaya "list", "pelamar/list", "detail/...", dan "group/..."
        |   tidak terbaca sebagai id.
        |--------------------------------------------------------------------------
        */

        Route::get('/jadwal-test/zoom/list', [JadwalTestZoomController::class, 'list'])
            ->middleware('permission:admin.jadwal-test.zoom.list')
            ->name('jadwal-test.zoom.list');

        Route::get('/jadwal-test/zoom/pelamar/list', [JadwalTestZoomController::class, 'pelamarList'])
            ->middleware('permission:admin.jadwal-test.zoom.options')
            ->name('jadwal-test.zoom.pelamar.list');

        Route::get('/jadwal-test/zoom/detail/{groupKey}', [JadwalTestZoomController::class, 'detail'])
            ->middleware('permission:admin.jadwal-test.zoom.detail')
            ->where('groupKey', '[A-Za-z0-9_\-]+')
            ->name('jadwal-test.zoom.detail');

        Route::get('/jadwal-test/zoom/detail-tanggal/{tanggal}', [JadwalTestZoomController::class, 'detailByTanggal'])
            ->middleware('permission:admin.jadwal-test.zoom.detail')
            ->where('tanggal', '[0-9]{4}-[0-9]{2}-[0-9]{2}')
            ->name('jadwal-test.zoom.detail-tanggal');

        Route::put('/jadwal-test/zoom/group/{groupKey}', [JadwalTestZoomController::class, 'updateGroup'])
            ->middleware('permission:admin.jadwal-test.zoom.update')
            ->where('groupKey', '[A-Za-z0-9_\-]+')
            ->name('jadwal-test.zoom.group.update');

        Route::delete('/jadwal-test/zoom/group/{groupKey}', [JadwalTestZoomController::class, 'destroyGroup'])
            ->middleware('permission:admin.jadwal-test.zoom.destroy')
            ->where('groupKey', '[A-Za-z0-9_\-]+')
            ->name('jadwal-test.zoom.group.destroy');

        Route::post('/jadwal-test/zoom', [JadwalTestZoomController::class, 'store'])
            ->middleware('permission:admin.jadwal-test.zoom.store')
            ->name('jadwal-test.zoom.store');

        Route::put('/jadwal-test/zoom/{id}', [JadwalTestZoomController::class, 'update'])
            ->middleware('permission:admin.jadwal-test.zoom.update')
            ->whereUuid('id')
            ->name('jadwal-test.zoom.update');

        Route::delete('/jadwal-test/zoom/{id}', [JadwalTestZoomController::class, 'destroy'])
            ->middleware('permission:admin.jadwal-test.zoom.destroy')
            ->whereUuid('id')
            ->name('jadwal-test.zoom.destroy');
    });
