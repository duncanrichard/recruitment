<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Report\ReportInterviewKandidatController;

Route::prefix('report-interview-kandidat')
    ->name('report-interview-kandidat.')
    ->group(function () {
        Route::get('/', [ReportInterviewKandidatController::class, 'index'])
            ->name('index');

        Route::get('/export', [ReportInterviewKandidatController::class, 'export'])
            ->name('export');
    });