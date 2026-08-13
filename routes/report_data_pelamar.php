<?php

use App\Http\Controllers\Report\ReportDataPelamarController;
use Illuminate\Support\Facades\Route;

Route::prefix('report-data-pelamar')
    ->name('report-data-pelamar.')
    ->group(function () {
        Route::get('/', [ReportDataPelamarController::class, 'index'])
            ->middleware('permission:admin.report.data-pelamar.list')
            ->name('index');

        Route::get('/export', [ReportDataPelamarController::class, 'export'])
            ->middleware(['permission:admin.report.data-pelamar.export', 'audit.access:exported'])
            ->name('export');
    });
