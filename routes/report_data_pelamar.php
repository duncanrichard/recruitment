<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Report\ReportDataPelamarController;

Route::prefix('report-data-pelamar')
    ->name('report-data-pelamar.')
    ->group(function () {
        Route::get('/', [ReportDataPelamarController::class, 'index'])
            ->name('index');

        Route::get('/export', [ReportDataPelamarController::class, 'export'])
            ->name('export');
    });