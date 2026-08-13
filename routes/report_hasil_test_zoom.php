<?php

use App\Http\Controllers\Report\ReportHasilTestZoomController;
use Illuminate\Support\Facades\Route;

Route::prefix('report-hasil-test-zoom')
    ->name('report-hasil-test-zoom.')
    ->group(function () {
        Route::get('/', [ReportHasilTestZoomController::class, 'index'])
            ->middleware('permission:admin.report.hasil-test-zoom.list')
            ->name('index');

        Route::get('/export', [ReportHasilTestZoomController::class, 'export'])
            ->middleware(['permission:admin.report.hasil-test-zoom.export', 'audit.access:exported'])
            ->name('export');
    });
