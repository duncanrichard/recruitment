<?php

use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard.index');

Route::prefix('admin/dashboard')
    ->name('admin.dashboard.')
    ->group(function () {
        Route::get('/summary', [DashboardController::class, 'summary'])
            ->name('summary');
    });
