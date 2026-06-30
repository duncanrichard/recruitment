<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ReviewManagementController;

Route::prefix('admin/review-management')
    ->name('admin.review-management.')
    ->controller(ReviewManagementController::class)
    ->group(function () {
        Route::get('/', 'index')
            ->middleware('permission:admin.review-management.list')
            ->name('index');

        Route::get('/list', 'list')
            ->middleware('permission:admin.review-management.list')
            ->name('list');

        Route::post('/review', 'store')
            ->middleware('permission:admin.review-management.store')
            ->name('store');

        Route::get('/review/{hasilReviewManagement}', 'show')
            ->middleware('permission:admin.review-management.show')
            ->whereUuid('hasilReviewManagement')
            ->name('show');

        Route::put('/review/{hasilReviewManagement}', 'update')
            ->middleware('permission:admin.review-management.update')
            ->whereUuid('hasilReviewManagement')
            ->name('update');

        Route::patch('/review/{hasilReviewManagement}', 'update')
            ->middleware('permission:admin.review-management.update')
            ->whereUuid('hasilReviewManagement')
            ->name('patch');

        Route::delete('/review/{hasilReviewManagement}', 'destroy')
            ->middleware('permission:admin.review-management.destroy')
            ->whereUuid('hasilReviewManagement')
            ->name('destroy');
    });
