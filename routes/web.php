<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/', [LoginController::class, 'index'])->name('login');
    Route::get('/login', [LoginController::class, 'index'])->name('login.page');
    Route::post('/login', [LoginController::class, 'login'])
        ->middleware('throttle:login')
        ->name('login.process');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
require __DIR__.'/pendaftaran.php';

/*
|--------------------------------------------------------------------------
| Protected Routes - Wajib Login
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/admin/auth-user', function () {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User belum login.',
                'data' => null,
                'user' => null,
            ], 401);
        }

        $user->loadMissing([
            'roles',
            'perusahaans',
            'divisi',
        ]);

        $roles = $user->roles->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'nama' => $role->name,
                'guard_name' => $role->guard_name,
            ];
        })->values();

        $perusahaans = $user->perusahaans->map(function ($perusahaan) {
            $kode = $perusahaan->kode ?? null;
            $nama = $perusahaan->nama_perusahaan
                ?? $perusahaan->perusahaan
                ?? $perusahaan->nama
                ?? null;

            $label = trim(($kode ? $kode.' - ' : '').($nama ?: ''));

            return [
                'id' => $perusahaan->id,
                'uuid' => $perusahaan->id,
                'kode' => $kode,
                'kode_perusahaan' => $kode,
                'nama_perusahaan' => $nama,
                'perusahaan' => $nama,
                'name' => $nama,
                'nama' => $nama,
                'label' => $label,
            ];
        })->values();

        $divisi = null;

        if ($user->divisi) {
            $namaDivisi = $user->divisi->nama_divisi
                ?? $user->divisi->divisi
                ?? $user->divisi->nama
                ?? null;

            $divisi = [
                'id' => $user->divisi->id,
                'uuid' => $user->divisi->id,
                'nama_divisi' => $namaDivisi,
                'divisi' => $namaDivisi,
                'name' => $namaDivisi,
                'nama' => $namaDivisi,
            ];
        }

        $data = [
            'uuid' => $user->uuid,
            'id' => $user->uuid,
            'name' => $user->name,
            'nama' => $user->name,
            'username' => $user->name,
            'email' => $user->email,

            'role' => $roles->first(),
            'role_name' => $roles->pluck('name')->filter()->implode(', '),
            'nama_role' => $roles->pluck('name')->filter()->implode(', '),
            'roles' => $roles,

            'divisi' => $divisi,
            'divisi_id' => $user->divisi_id,
            'nama_divisi' => $divisi['nama_divisi'] ?? null,

            'perusahaans' => $perusahaans,
            'perusahaan' => $perusahaans,
            'data_perusahaan' => $perusahaans,

            'perusahaan_id' => $perusahaans->first()['id'] ?? $user->perusahaan_id ?? null,
            'kode_perusahaan' => $perusahaans->first()['kode'] ?? null,
            'nama_perusahaan' => $perusahaans->first()['nama_perusahaan'] ?? null,

            'perusahaan_ids' => $perusahaans->pluck('id')->values(),
            'perusahaan_kode' => $perusahaans->pluck('kode')->filter()->implode(', '),
            'perusahaan_label' => $perusahaans->pluck('label')->filter()->implode(', '),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Data user login berhasil diambil.',
            'data' => $data,
            'user' => $data,
        ]);
    })->name('admin.auth-user');

    require __DIR__.'/admin.php';
    require __DIR__.'/dashboard.php';
    require __DIR__.'/data_pelamar.php';
    require __DIR__.'/master_data_posisi.php';
    require __DIR__.'/master_data_pendidikan.php';
    require __DIR__.'/master_data_agama.php';
    require __DIR__.'/master_data_kewarganegaraan.php';
    require __DIR__.'/master_data_status_pernikahan.php';
    require __DIR__.'/master_data_opsi_kacamata.php';
    require __DIR__.'/master_data_sumber_informasi.php';
    require __DIR__.'/master_data_perusahaan.php';
    require __DIR__.'/jadwal_test_zoom.php';
    require __DIR__.'/jadwal_test_mmpi.php';
    require __DIR__.'/daftar_hadir_zoom.php';
    require __DIR__.'/daftar_hadir_mmpi.php';
    require __DIR__.'/master_data_jabatan.php';
    require __DIR__.'/master_data_divisi.php';
    require __DIR__.'/jadwal_interview.php';
    require __DIR__.'/interviewer.php';
    require __DIR__.'/interview_kandidat.php';
    require __DIR__.'/account_role.php';
    require __DIR__.'/review_management.php';
    require __DIR__.'/account_user.php';
    require __DIR__.'/account_permission.php';
    require __DIR__.'/permintaan_kandidat.php';
    require __DIR__.'/jadwal_offering_letter.php';
    require __DIR__.'/recruitment_audit.php';
    require __DIR__.'/ai_recruitment.php';

    require __DIR__.'/report_data_pelamar.php';
    require __DIR__.'/report_hasil_test_zoom.php';
    require __DIR__.'/report_hasil_test_mmpi.php';
    require __DIR__.'/report_interview_kandidat.php';
    require __DIR__.'/report_offering_letter.php';
    require __DIR__.'/report_interviewer.php';
});
