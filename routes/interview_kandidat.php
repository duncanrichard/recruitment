<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\RangkaianInterview\InterviewKandidatController;

Route::prefix('admin/rangkaian-interview/kandidat')
    ->name('admin.rangkaian-interview.kandidat.')
    ->controller(InterviewKandidatController::class)
    ->group(function () {
        /*
        |--------------------------------------------------------------------------
        | List Data Kandidat Interview
        |--------------------------------------------------------------------------
        */
        Route::get('/list', 'list')
            ->middleware('permission:admin.rangkaian-interview.kandidat.list')
            ->name('list');

        /*
        |--------------------------------------------------------------------------
        | Options
        |--------------------------------------------------------------------------
        */
        Route::get('/jadwal-options', 'jadwalOptions')
            ->middleware('permission:admin.rangkaian-interview.kandidat.options')
            ->name('jadwal-options');

        Route::get('/kandidat-options', 'kandidatOptions')
            ->middleware('permission:admin.rangkaian-interview.kandidat.options')
            ->name('kandidat-options');

        /*
        |--------------------------------------------------------------------------
        | Detail Group Jadwal Interview
        |--------------------------------------------------------------------------
        */
        Route::get('/{jadwalInterviewId}/detail', 'detail')
            ->middleware('permission:admin.rangkaian-interview.kandidat.detail')
            ->whereUuid('jadwalInterviewId')
            ->name('detail');

        /*
        |--------------------------------------------------------------------------
        | Store Kandidat ke Jadwal Interview
        |--------------------------------------------------------------------------
        */
        Route::post('/', 'store')
            ->middleware('permission:admin.rangkaian-interview.kandidat.store')
            ->name('store');

        /*
        |--------------------------------------------------------------------------
        | Update Kandidat Dalam Jadwal Interview
        |--------------------------------------------------------------------------
        */
        Route::put('/{jadwalInterviewId}', 'update')
            ->middleware('permission:admin.rangkaian-interview.kandidat.update')
            ->whereUuid('jadwalInterviewId')
            ->name('update');

        /*
        |--------------------------------------------------------------------------
        | Update Tanggal Interview
        |--------------------------------------------------------------------------
        */
        Route::patch('/{jadwalInterviewId}/tanggal', 'updateTanggal')
            ->middleware('permission:admin.rangkaian-interview.kandidat.update-tanggal')
            ->whereUuid('jadwalInterviewId')
            ->name('update-tanggal');

        /*
        |--------------------------------------------------------------------------
        | Kirim Reminder Interview ke Kandidat
        |--------------------------------------------------------------------------
        | Dipakai tombol Reminder / Kirim Reminder Kandidat di React.
        |
        | Endpoint:
        | POST /admin/rangkaian-interview/kandidat/{jadwalInterviewId}/reminder-kandidat
        |--------------------------------------------------------------------------
        */
        Route::post('/{jadwalInterviewId}/reminder-kandidat', 'kirimReminderKandidat')
            ->middleware('permission:admin.rangkaian-interview.kandidat.reminder-kandidat')
            ->whereUuid('jadwalInterviewId')
            ->name('reminder-kandidat');

        /*
        |--------------------------------------------------------------------------
        | Update Status / Hasil / Catatan Kandidat Interview
        |--------------------------------------------------------------------------
        */
        Route::patch('/{jadwalInterviewId}/kandidat/{pivotId}/hasil', 'updateHasil')
            ->middleware('permission:admin.rangkaian-interview.kandidat.update-hasil')
            ->whereUuid('jadwalInterviewId')
            ->whereUuid('pivotId')
            ->name('update-hasil');

        /*
        |--------------------------------------------------------------------------
        | Hapus 1 Kandidat dari Jadwal Interview
        |--------------------------------------------------------------------------
        */
        Route::delete('/{jadwalInterviewId}/kandidat/{pivotId}', 'destroyKandidat')
            ->middleware('permission:admin.rangkaian-interview.kandidat.destroy-kandidat')
            ->whereUuid('jadwalInterviewId')
            ->whereUuid('pivotId')
            ->name('destroy-kandidat');

        /*
        |--------------------------------------------------------------------------
        | Hapus Group Jadwal Interview
        |--------------------------------------------------------------------------
        */
        Route::delete('/{jadwalInterviewId}', 'destroy')
            ->middleware('permission:admin.rangkaian-interview.kandidat.destroy')
            ->whereUuid('jadwalInterviewId')
            ->name('destroy');
    });