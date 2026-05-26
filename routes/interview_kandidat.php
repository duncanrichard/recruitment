<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\RangkaianInterview\InterviewKandidatController;

Route::prefix('admin/rangkaian-interview/kandidat')
    ->name('admin.rangkaian-interview.kandidat.')
    ->group(function () {
        Route::get('/list', [InterviewKandidatController::class, 'list'])->name('list');
        Route::get('/jadwal-options', [InterviewKandidatController::class, 'jadwalOptions'])->name('jadwal-options');
        Route::get('/kandidat-options', [InterviewKandidatController::class, 'kandidatOptions'])->name('kandidat-options');
        Route::get('/{jadwalInterviewId}/detail', [InterviewKandidatController::class, 'detail'])->name('detail');

        Route::post('/', [InterviewKandidatController::class, 'store'])->name('store');
        Route::put('/{jadwalInterviewId}', [InterviewKandidatController::class, 'update'])->name('update');
        Route::patch('/{jadwalInterviewId}/tanggal', [InterviewKandidatController::class, 'updateTanggal'])->name('update-tanggal');
        Route::patch('/{jadwalInterviewId}/kandidat/{pivotId}/hasil', [InterviewKandidatController::class, 'updateHasil'])->name('update-hasil');
        Route::delete('/{jadwalInterviewId}/kandidat/{pivotId}', [InterviewKandidatController::class, 'destroyKandidat'])->name('destroy-kandidat');
        Route::delete('/{jadwalInterviewId}', [InterviewKandidatController::class, 'destroy'])->name('destroy');
    });
