<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\RangkaianInterview\InterviewerController;

Route::prefix('admin/rangkaian-interview/interviewer')
    ->name('admin.rangkaian-interview.interviewer.')
    ->group(function () {
        Route::get('/list', [InterviewerController::class, 'list'])->name('list');
        Route::get('/options', [InterviewerController::class, 'options'])->name('options');

        Route::post('/', [InterviewerController::class, 'store'])->name('store');
        Route::put('/{id}', [InterviewerController::class, 'update'])->name('update');
        Route::delete('/{id}', [InterviewerController::class, 'destroy'])->name('destroy');
    });