<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard.index');

Route::prefix('admin/dashboard')
    ->name('admin.dashboard.')
    ->group(function () {
        Route::get('/summary', [DashboardController::class, 'summary'])
            ->name('summary');
    });