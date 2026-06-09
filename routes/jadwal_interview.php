<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\RangkaianInterview\JadwalInterviewController;

Route::prefix('admin/rangkaian-interview/jadwal-interview')
    ->name('admin.rangkaian-interview.jadwal-interview.')
    ->group(function () {
        Route::get('/list', [JadwalInterviewController::class, 'list'])
            ->middleware('permission:admin.rangkaian-interview.jadwal-interview.list')
            ->name('list');

        Route::get('/interviewers', [JadwalInterviewController::class, 'interviewers'])
            ->middleware('permission:admin.rangkaian-interview.jadwal-interview.options')
            ->name('interviewers');

        Route::post('/', [JadwalInterviewController::class, 'store'])
            ->middleware('permission:admin.rangkaian-interview.jadwal-interview.store')
            ->name('store');

        Route::put('/{id}', [JadwalInterviewController::class, 'update'])
            ->middleware('permission:admin.rangkaian-interview.jadwal-interview.update')
            ->whereUuid('id')
            ->name('update');

        Route::delete('/{id}', [JadwalInterviewController::class, 'destroy'])
            ->middleware('permission:admin.rangkaian-interview.jadwal-interview.destroy')
            ->whereUuid('id')
            ->name('destroy');
    });