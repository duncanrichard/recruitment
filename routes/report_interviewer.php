<?php

use App\Http\Controllers\Report\ReportInterviewerController;
use Illuminate\Support\Facades\Route;

Route::get('/report-interviewer', [ReportInterviewerController::class, 'index'])
    ->middleware('permission:admin.report.interviewer.list')
    ->name('report.interviewer.index');

Route::get('/report-interviewer/export', [ReportInterviewerController::class, 'export'])
    ->middleware(['permission:admin.report.interviewer.export', 'audit.access:exported'])
    ->name('report.interviewer.export');
