<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Report\ReportOfferingLetterController;

Route::get('/report-offering-letter', [ReportOfferingLetterController::class, 'index'])
    ->name('report.offering-letter.index');

Route::get('/report-offering-letter/export', [ReportOfferingLetterController::class, 'export'])
    ->name('report.offering-letter.export');