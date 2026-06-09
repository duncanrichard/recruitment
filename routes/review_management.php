<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ReviewManagementController;

Route::prefix('admin/review-management')
    ->name('admin.review-management.')
    ->group(function () {
        Route::get('/', [ReviewManagementController::class, 'index'])
            ->middleware('permission:admin.review-management.list')
            ->name('index');

        Route::get('/list', [ReviewManagementController::class, 'list'])
            ->middleware('permission:admin.review-management.list')
            ->name('list');

        Route::post('/review', [ReviewManagementController::class, 'store'])
            ->middleware('permission:admin.review-management.store')
            ->name('store');

        Route::get('/review/{hasilReviewManagement}', [ReviewManagementController::class, 'show'])
            ->middleware('permission:admin.review-management.show')
            ->whereUuid('hasilReviewManagement')
            ->name('show');

        Route::put('/review/{hasilReviewManagement}', [ReviewManagementController::class, 'update'])
            ->middleware('permission:admin.review-management.update')
            ->whereUuid('hasilReviewManagement')
            ->name('update');

        Route::delete('/review/{hasilReviewManagement}', [ReviewManagementController::class, 'destroy'])
            ->middleware('permission:admin.review-management.destroy')
            ->whereUuid('hasilReviewManagement')
            ->name('destroy');
    });