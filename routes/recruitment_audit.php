<?php

use App\Http\Controllers\Admin\RecruitmentAuditController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/recruitment-audits')
    ->name('admin.recruitment-audits.')
    ->group(function () {
        Route::get('/', [RecruitmentAuditController::class, 'index'])
            ->middleware('permission:admin.recruitment-audit.list')
            ->name('index');
        Route::get('/export', [RecruitmentAuditController::class, 'export'])
            ->middleware(['permission:admin.recruitment-audit.export', 'audit.access:exported'])
            ->name('export');
        Route::get('/{audit}', [RecruitmentAuditController::class, 'show'])
            ->middleware('permission:admin.recruitment-audit.detail')
            ->whereUuid('audit')
            ->name('show');
    });
