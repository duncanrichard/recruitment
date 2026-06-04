<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Report\ReportHasilTestMmpiController;

Route::prefix('report-hasil-test-mmpi')
    ->name('report-hasil-test-mmpi.')
    ->group(function () {
        Route::get('/', [ReportHasilTestMmpiController::class, 'index'])
            ->name('index');

        Route::get('/export', [ReportHasilTestMmpiController::class, 'export'])
            ->name('export');
    });