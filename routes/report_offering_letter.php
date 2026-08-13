<?php

use App\Http\Controllers\Report\ReportOfferingLetterController;
use Illuminate\Support\Facades\Route;

Route::get('/report-offering-letter', [ReportOfferingLetterController::class, 'index'])
    ->middleware('permission:admin.report.offering-letter.list')
    ->name('report.offering-letter.index');

Route::get('/report-offering-letter/export', [ReportOfferingLetterController::class, 'export'])
    ->middleware(['permission:admin.report.offering-letter.export', 'audit.access:exported'])
    ->name('report.offering-letter.export');
