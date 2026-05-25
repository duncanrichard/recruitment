<?php

use App\Http\Controllers\Admin\JadwalTest\MmpiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tambahkan route ini di routes/web.php pada group admin/auth yang sudah ada.
|--------------------------------------------------------------------------
*/

Route::prefix('admin/jadwal-test/mmpi')->group(function () {
    Route::get('/', [MmpiController::class, 'index'])
        ->name('admin.jadwal-test.mmpi.index');

    Route::get('/list', [MmpiController::class, 'list'])
        ->name('admin.jadwal-test.mmpi.list');

    Route::get('/kandidat-lolos-zoom', [MmpiController::class, 'kandidatLolosZoom'])
        ->name('admin.jadwal-test.mmpi.kandidat-lolos-zoom');

    Route::post('/', [MmpiController::class, 'store'])
        ->name('admin.jadwal-test.mmpi.store');

    Route::delete('/{id}', [MmpiController::class, 'destroy'])
        ->name('admin.jadwal-test.mmpi.destroy');
});
