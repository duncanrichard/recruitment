<?php

use App\Http\Controllers\Admin\AiRecruitmentController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/ai-recruitment')
    ->name('admin.ai-recruitment.')
    ->group(function () {
        Route::get('/candidates', [AiRecruitmentController::class, 'candidates'])
            ->middleware('permission:admin.ai-recruitment.list')
            ->name('candidates');

        Route::post('/analyze', [AiRecruitmentController::class, 'analyze'])
            ->middleware(['permission:admin.ai-recruitment.analyze', 'throttle:10,1'])
            ->name('analyze');
    });
