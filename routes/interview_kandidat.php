<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\RangkaianInterview\InterviewKandidatController;

Route::prefix('admin/rangkaian-interview/kandidat')
    ->name('admin.rangkaian-interview.kandidat.')
    ->group(function () {
        Route::get('/list', [InterviewKandidatController::class, 'list'])
            ->middleware('permission:admin.rangkaian-interview.kandidat.list')
            ->name('list');

        Route::get('/jadwal-options', [InterviewKandidatController::class, 'jadwalOptions'])
            ->middleware('permission:admin.rangkaian-interview.kandidat.options')
            ->name('jadwal-options');

        Route::get('/kandidat-options', [InterviewKandidatController::class, 'kandidatOptions'])
            ->middleware('permission:admin.rangkaian-interview.kandidat.options')
            ->name('kandidat-options');

        Route::get('/{jadwalInterviewId}/detail', [InterviewKandidatController::class, 'detail'])
            ->middleware('permission:admin.rangkaian-interview.kandidat.detail')
            ->whereUuid('jadwalInterviewId')
            ->name('detail');

        Route::post('/', [InterviewKandidatController::class, 'store'])
            ->middleware('permission:admin.rangkaian-interview.kandidat.store')
            ->name('store');

        Route::put('/{jadwalInterviewId}', [InterviewKandidatController::class, 'update'])
            ->middleware('permission:admin.rangkaian-interview.kandidat.update')
            ->whereUuid('jadwalInterviewId')
            ->name('update');

        Route::patch('/{jadwalInterviewId}/tanggal', [InterviewKandidatController::class, 'updateTanggal'])
            ->middleware('permission:admin.rangkaian-interview.kandidat.update-tanggal')
            ->whereUuid('jadwalInterviewId')
            ->name('update-tanggal');

        Route::patch('/{jadwalInterviewId}/kandidat/{pivotId}/hasil', [InterviewKandidatController::class, 'updateHasil'])
            ->middleware('permission:admin.rangkaian-interview.kandidat.update-hasil')
            ->whereUuid('jadwalInterviewId')
            ->whereUuid('pivotId')
            ->name('update-hasil');

        Route::delete('/{jadwalInterviewId}/kandidat/{pivotId}', [InterviewKandidatController::class, 'destroyKandidat'])
            ->middleware('permission:admin.rangkaian-interview.kandidat.destroy-kandidat')
            ->whereUuid('jadwalInterviewId')
            ->whereUuid('pivotId')
            ->name('destroy-kandidat');

        Route::delete('/{jadwalInterviewId}', [InterviewKandidatController::class, 'destroy'])
            ->middleware('permission:admin.rangkaian-interview.kandidat.destroy')
            ->whereUuid('jadwalInterviewId')
            ->name('destroy');
    });