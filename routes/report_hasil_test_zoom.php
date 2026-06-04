<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Report\ReportHasilTestZoomController;

Route::prefix('report-hasil-test-zoom')
    ->name('report-hasil-test-zoom.')
    ->group(function () {
        Route::get('/', [ReportHasilTestZoomController::class, 'index'])
            ->name('index');

        Route::get('/export', [ReportHasilTestZoomController::class, 'export'])
            ->name('export');
    });