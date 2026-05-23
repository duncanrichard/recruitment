<?php

use App\Http\Controllers\Admin\JadwalTestZoomController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Jadwal Test Zoom
    |--------------------------------------------------------------------------
    | Urutan route penting:
    | - route statis/detail/group diletakkan sebelum route dinamis {id}
    | - supaya "list", "pelamar/list", "detail/..." dan "group/..." tidak terbaca
    |   sebagai id.
    |--------------------------------------------------------------------------
    */

    Route::get('/jadwal-test/zoom/list', [JadwalTestZoomController::class, 'list'])
        ->name('jadwal-test.zoom.list');

    Route::get('/jadwal-test/zoom/pelamar/list', [JadwalTestZoomController::class, 'pelamarList'])
        ->name('jadwal-test.zoom.pelamar.list');

    Route::get('/jadwal-test/zoom/detail/{groupKey}', [JadwalTestZoomController::class, 'detail'])
        ->name('jadwal-test.zoom.detail');

    Route::get('/jadwal-test/zoom/detail-tanggal/{tanggal}', [JadwalTestZoomController::class, 'detailByTanggal'])
        ->name('jadwal-test.zoom.detail-tanggal');

    /*
    |--------------------------------------------------------------------------
    | Update Group Jadwal
    |--------------------------------------------------------------------------
    | Dipakai untuk update jadwal + link Zoom sekaligus untuk semua pelamar
    | pada jadwal test yang sama.
    |--------------------------------------------------------------------------
    */
    Route::put('/jadwal-test/zoom/group/{groupKey}', [JadwalTestZoomController::class, 'updateGroup'])
        ->name('jadwal-test.zoom.group.update');

    Route::delete('/jadwal-test/zoom/group/{groupKey}', [JadwalTestZoomController::class, 'destroyGroup'])
        ->name('jadwal-test.zoom.group.destroy');

    Route::post('/jadwal-test/zoom', [JadwalTestZoomController::class, 'store'])
        ->name('jadwal-test.zoom.store');

    Route::put('/jadwal-test/zoom/{id}', [JadwalTestZoomController::class, 'update'])
        ->name('jadwal-test.zoom.update');

    Route::delete('/jadwal-test/zoom/{id}', [JadwalTestZoomController::class, 'destroy'])
        ->name('jadwal-test.zoom.destroy');
});
