<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\RangkaianInterview\JadwalInterviewController;

Route::prefix('admin/rangkaian-interview/jadwal-interview')
    ->name('admin.rangkaian-interview.jadwal-interview.')
    ->group(function () {
        Route::get('/list', [JadwalInterviewController::class, 'list'])->name('list');
        Route::get('/interviewers', [JadwalInterviewController::class, 'interviewers'])->name('interviewers');

        Route::post('/', [JadwalInterviewController::class, 'store'])->name('store');
        Route::put('/{id}', [JadwalInterviewController::class, 'update'])->name('update');
        Route::delete('/{id}', [JadwalInterviewController::class, 'destroy'])->name('destroy');
    });