<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ReviewManagementController;

Route::prefix('admin/review-management')
    ->name('admin.review-management.')
    ->group(function () {
        Route::get('/', [ReviewManagementController::class, 'index'])->name('index');
        Route::get('/list', [ReviewManagementController::class, 'list'])->name('list');
        Route::post('/review', [ReviewManagementController::class, 'store'])->name('store');
        Route::get('/review/{hasilReviewManagement}', [ReviewManagementController::class, 'show'])->name('show');
        Route::put('/review/{hasilReviewManagement}', [ReviewManagementController::class, 'update'])->name('update');
        Route::delete('/review/{hasilReviewManagement}', [ReviewManagementController::class, 'destroy'])->name('destroy');
    });