<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Report\ReportInterviewerController;

Route::get('/report-interviewer', [ReportInterviewerController::class, 'index'])
    ->name('report.interviewer.index');

Route::get('/report-interviewer/export', [ReportInterviewerController::class, 'export'])
    ->name('report.interviewer.export');