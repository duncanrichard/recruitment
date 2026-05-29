<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;

Route::get('/', [LoginController::class, 'index'])->name('login');
Route::get('/login', [LoginController::class, 'index'])->name('login.page');

require __DIR__ . '/pendaftaran.php';
require __DIR__ . '/admin.php';
require __DIR__ . '/data_pelamar.php';
require __DIR__ . '/dashboard.php';
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