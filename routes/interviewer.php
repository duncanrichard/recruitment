<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\RangkaianInterview\InterviewerController;

Route::prefix('admin/rangkaian-interview/interviewer')
    ->name('admin.rangkaian-interview.interviewer.')
    ->group(function () {
        Route::get('/list', [InterviewerController::class, 'list'])
            ->middleware('permission:admin.rangkaian-interview.interviewer.list')
            ->name('list');

        Route::get('/options', [InterviewerController::class, 'options'])
            ->middleware('permission:admin.rangkaian-interview.interviewer.options')
            ->name('options');

        Route::post('/', [InterviewerController::class, 'store'])
            ->middleware('permission:admin.rangkaian-interview.interviewer.store')
            ->name('store');

        Route::put('/{id}', [InterviewerController::class, 'update'])
            ->middleware('permission:admin.rangkaian-interview.interviewer.update')
            ->whereUuid('id')
            ->name('update');

        Route::delete('/{id}', [InterviewerController::class, 'destroy'])
            ->middleware('permission:admin.rangkaian-interview.interviewer.destroy')
            ->whereUuid('id')
            ->name('destroy');
    });