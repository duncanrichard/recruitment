<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;

Route::middleware('guest')->group(function () {
    Route::get('/', [LoginController::class, 'index'])->name('login');
    Route::get('/login', [LoginController::class, 'index'])->name('login.page');
    Route::post('/login', [LoginController::class, 'login'])->name('login.process');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
require __DIR__ . '/pendaftaran.php';

/*
|--------------------------------------------------------------------------
| Protected Routes - Wajib Login
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    require __DIR__ . '/admin.php';
    require __DIR__ . '/dashboard.php';
    require __DIR__ . '/data_pelamar.php';
    require __DIR__ . '/master_data_posisi.php';
    require __DIR__ . '/master_data_pendidikan.php';
    require __DIR__ . '/master_data_agama.php';
    require __DIR__ . '/master_data_kewarganegaraan.php';
    require __DIR__ . '/master_data_status_pernikahan.php';
    require __DIR__ . '/master_data_opsi_kacamata.php';
    require __DIR__ . '/master_data_sumber_informasi.php';
    require __DIR__ . '/master_data_perusahaan.php';
    require __DIR__ . '/jadwal_test_zoom.php';
    require __DIR__ . '/jadwal_test_mmpi.php';
    require __DIR__ . '/daftar_hadir_zoom.php';
    require __DIR__ . '/daftar_hadir_mmpi.php';
    require __DIR__ . '/master_data_jabatan.php';
    require __DIR__ . '/master_data_divisi.php';
    require __DIR__ . '/jadwal_interview.php';
    require __DIR__ . '/interviewer.php';
    require __DIR__ . '/interview_kandidat.php';
    require __DIR__ . '/account_role.php';
    require __DIR__ . '/review_management.php';
    require __DIR__ . '/account_user.php';
    require __DIR__ . '/account_permission.php';
    require __DIR__ . '/permintaan_kandidat.php';

    require __DIR__ . '/report_data_pelamar.php';
    require __DIR__ . '/report_hasil_test_zoom.php';
    require __DIR__ . '/report_hasil_test_mmpi.php';
    require __DIR__ . '/report_interview_kandidat.php';
});