<?php

use App\Http\Controllers\Report\ReportHasilTestMmpiController;
use Illuminate\Support\Facades\Route;

Route::prefix('report-hasil-test-mmpi')
    ->name('report-hasil-test-mmpi.')
    ->group(function () {
        Route::get('/', [ReportHasilTestMmpiController::class, 'index'])
            ->middleware('permission:admin.report.hasil-test-mmpi.list')
            ->name('index');

        Route::get('/export', [ReportHasilTestMmpiController::class, 'export'])
            ->middleware(['permission:admin.report.hasil-test-mmpi.export', 'audit.access:exported'])
            ->name('export');
    });
