<?php

use App\Http\Controllers\Admin\JadwalTestZoomController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
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

    /*
    |--------------------------------------------------------------------------
    | List Data Jadwal Zoom
    |--------------------------------------------------------------------------
    */
    Route::get('/jadwal-test/zoom/list', [JadwalTestZoomController::class, 'list'])
        ->name('jadwal-test.zoom.list');

    /*
    |--------------------------------------------------------------------------
    | List Pelamar yang Bisa Dijadwalkan
    |--------------------------------------------------------------------------
    */
    Route::get('/jadwal-test/zoom/pelamar/list', [JadwalTestZoomController::class, 'pelamarList'])
        ->name('jadwal-test.zoom.pelamar.list');

    /*
    |--------------------------------------------------------------------------
    | Detail Group Jadwal
    |--------------------------------------------------------------------------
    */
    Route::get('/jadwal-test/zoom/detail/{groupKey}', [JadwalTestZoomController::class, 'detail'])
        ->where('groupKey', '[A-Za-z0-9_\-]+')
        ->name('jadwal-test.zoom.detail');

    /*
    |--------------------------------------------------------------------------
    | Detail Jadwal Berdasarkan Tanggal
    |--------------------------------------------------------------------------
    */
    Route::get('/jadwal-test/zoom/detail-tanggal/{tanggal}', [JadwalTestZoomController::class, 'detailByTanggal'])
        ->where('tanggal', '[0-9]{4}-[0-9]{2}-[0-9]{2}')
        ->name('jadwal-test.zoom.detail-tanggal');

    /*
    |--------------------------------------------------------------------------
    | Update Group Jadwal
    |--------------------------------------------------------------------------
    | Dipakai untuk edit jadwal + link Zoom semua pelamar
    | pada group jadwal yang sama.
    |--------------------------------------------------------------------------
    */
    Route::put('/jadwal-test/zoom/group/{groupKey}', [JadwalTestZoomController::class, 'updateGroup'])
        ->where('groupKey', '[A-Za-z0-9_\-]+')
        ->name('jadwal-test.zoom.group.update');

    /*
    |--------------------------------------------------------------------------
    | Hapus Group Jadwal
    |--------------------------------------------------------------------------
    | Dipakai untuk menghapus semua jadwal pelamar
    | pada group jadwal yang sama.
    |--------------------------------------------------------------------------
    */
    Route::delete('/jadwal-test/zoom/group/{groupKey}', [JadwalTestZoomController::class, 'destroyGroup'])
        ->where('groupKey', '[A-Za-z0-9_\-]+')
        ->name('jadwal-test.zoom.group.destroy');

    /*
    |--------------------------------------------------------------------------
    | Tambah Jadwal Zoom
    |--------------------------------------------------------------------------
    */
    Route::post('/jadwal-test/zoom', [JadwalTestZoomController::class, 'store'])
        ->name('jadwal-test.zoom.store');

    /*
    |--------------------------------------------------------------------------
    | Update Satu Jadwal Zoom
    |--------------------------------------------------------------------------
    | Diletakkan setelah route group/detail/list agar tidak bentrok.
    |--------------------------------------------------------------------------
    */
    Route::put('/jadwal-test/zoom/{id}', [JadwalTestZoomController::class, 'update'])
        ->whereUuid('id')
        ->name('jadwal-test.zoom.update');

    /*
    |--------------------------------------------------------------------------
    | Hapus Satu Jadwal Zoom
    |--------------------------------------------------------------------------
    | Diletakkan setelah route group/detail/list agar tidak bentrok.
    |--------------------------------------------------------------------------
    */
    Route::delete('/jadwal-test/zoom/{id}', [JadwalTestZoomController::class, 'destroy'])
        ->whereUuid('id')
        ->name('jadwal-test.zoom.destroy');
});