<?php

use App\Http\Controllers\Report\ReportInterviewKandidatController;
use Illuminate\Support\Facades\Route;

Route::prefix('report-interview-kandidat')
    ->name('report-interview-kandidat.')
    ->group(function () {
        Route::get('/', [ReportInterviewKandidatController::class, 'index'])
            ->middleware('permission:admin.report.interview-kandidat.list')
            ->name('index');

        Route::get('/export', [ReportInterviewKandidatController::class, 'export'])
            ->middleware(['permission:admin.report.interview-kandidat.export', 'audit.access:exported'])
            ->name('export');
    });
