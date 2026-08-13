<?php

use App\Http\Controllers\CekTahapanPelamarController;
use App\Http\Controllers\PendaftaranController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Master Data Pendaftaran
|--------------------------------------------------------------------------
*/
Route::get('/pendaftaran/api/master/pendaftaran', [PendaftaranController::class, 'masterPendaftaran'])
    ->name('pendaftaran.api.master.pendaftaran');

Route::get('/pendaftaran/api/master/pendidikan', [PendaftaranController::class, 'masterPendidikan'])
    ->name('pendaftaran.api.master.pendidikan');

Route::get('/pendaftaran/api/master/status-pernikahan', [PendaftaranController::class, 'masterStatusPernikahan'])
    ->name('pendaftaran.api.master.status-pernikahan');

/*
|--------------------------------------------------------------------------
| API Wilayah
|--------------------------------------------------------------------------
*/
Route::get('/pendaftaran/api/wilayah/provinces', [PendaftaranController::class, 'wilayahProvinces'])
    ->name('pendaftaran.api.wilayah.provinces');

Route::get('/pendaftaran/api/wilayah/regencies/{province_code}', [PendaftaranController::class, 'wilayahRegencies'])
    ->name('pendaftaran.api.wilayah.regencies');

Route::get('/pendaftaran/api/wilayah/districts/{regency_code}', [PendaftaranController::class, 'wilayahDistricts'])
    ->name('pendaftaran.api.wilayah.districts');

Route::get('/pendaftaran/api/wilayah/villages/{district_code}', [PendaftaranController::class, 'wilayahVillages'])
    ->name('pendaftaran.api.wilayah.villages');

/*
|--------------------------------------------------------------------------
| API Token Pendaftaran
|--------------------------------------------------------------------------
*/
Route::get('/pendaftaran/api/token/{token}', [PendaftaranController::class, 'findByToken'])
    ->middleware('throttle:candidate-read')
    ->name('pendaftaran.api.token');

/*
|--------------------------------------------------------------------------
| API Cek Tahapan Pelamar
|--------------------------------------------------------------------------
*/
Route::get('/pendaftaran/api/token/{token}/tahapan', [CekTahapanPelamarController::class, 'tahapanByToken'])
    ->middleware('throttle:candidate-read')
    ->name('pendaftaran.api.token.tahapan');

Route::get('/pendaftaran/api/token/{token}/cek-tahapan', [CekTahapanPelamarController::class, 'cekTahapanByToken'])
    ->middleware('throttle:candidate-read')
    ->name('pendaftaran.api.token.cek-tahapan');

Route::patch(
    '/pendaftaran/api/token/{token}/jadwal-test/{jadwalTest}/kehadiran',
    [CekTahapanPelamarController::class, 'updateKehadiranJadwalTest']
)
    ->whereUuid('jadwalTest')
    ->middleware('throttle:candidate-write')
    ->name('pendaftaran.api.token.jadwal-test.kehadiran');

Route::patch(
    '/pendaftaran/api/token/{token}/jadwal-test-mmpi/{jadwalTestMmpi}/kehadiran',
    [CekTahapanPelamarController::class, 'updateKehadiranJadwalTestMmpi']
)
    ->whereUuid('jadwalTestMmpi')
    ->middleware('throttle:candidate-write')
    ->name('pendaftaran.api.token.jadwal-test-mmpi.kehadiran');

/*
|--------------------------------------------------------------------------
| Upload Dokumen Interview Kandidat
|--------------------------------------------------------------------------
| POST dipakai untuk upload CV dan Foto.
| GET fallback dibuat supaya tidak error Method Not Allowed kalau URL terbuka.
|--------------------------------------------------------------------------
*/
Route::post(
    '/pendaftaran/api/token/{token}/jadwal-interview/{jadwalInterviewKandidat}/dokumen',
    [CekTahapanPelamarController::class, 'uploadDokumenInterview']
)
    ->whereUuid('jadwalInterviewKandidat')
    ->middleware('throttle:candidate-write')
    ->name('pendaftaran.api.token.jadwal-interview.dokumen.upload');

Route::get(
    '/pendaftaran/api/token/{token}/jadwal-interview/{jadwalInterviewKandidat}/dokumen',
    function (string $token) {
        return redirect()->route('pendaftaran.cek-tahapan', [
            'token' => $token,
        ]);
    }
)
    ->whereUuid('jadwalInterviewKandidat')
    ->name('pendaftaran.api.token.jadwal-interview.dokumen.fallback');

/*
|--------------------------------------------------------------------------
| Step 1 - Data Diri
|--------------------------------------------------------------------------
*/
Route::patch('/pendaftaran/api/token/{token}/data-diri', [PendaftaranController::class, 'updateDataDiriByToken'])
    ->middleware('throttle:candidate-write')
    ->name('pendaftaran.api.token.data-diri.update');

Route::get('/pendaftaran/api/token/{token}/data-diri', function (string $token) {
    return redirect()->route('pendaftaran.show', ['token' => $token]);
})->name('pendaftaran.api.token.data-diri.show');

/*
|--------------------------------------------------------------------------
| Step 2 - Riwayat Keluarga
|--------------------------------------------------------------------------
*/
Route::patch('/pendaftaran/api/token/{token}/riwayat-keluarga', [PendaftaranController::class, 'updateRiwayatKeluargaByToken'])
    ->middleware('throttle:candidate-write')
    ->name('pendaftaran.api.token.riwayat-keluarga.update');

Route::get('/pendaftaran/api/token/{token}/riwayat-keluarga', function (string $token) {
    return redirect()->route('pendaftaran.show', ['token' => $token]);
})->name('pendaftaran.api.token.riwayat-keluarga.show');

/*
|--------------------------------------------------------------------------
| Step 3 - Riwayat Kesehatan
|--------------------------------------------------------------------------
*/
Route::patch('/pendaftaran/api/token/{token}/riwayat-kesehatan', [PendaftaranController::class, 'updateRiwayatKesehatanByToken'])
    ->middleware('throttle:candidate-write')
    ->name('pendaftaran.api.token.riwayat-kesehatan.update');

Route::get('/pendaftaran/api/token/{token}/riwayat-kesehatan', function (string $token) {
    return redirect()->route('pendaftaran.show', ['token' => $token]);
})->name('pendaftaran.api.token.riwayat-kesehatan.show');

/*
|--------------------------------------------------------------------------
| Step 4 - Riwayat Pekerjaan
|--------------------------------------------------------------------------
*/
Route::patch('/pendaftaran/api/token/{token}/riwayat-pekerjaan', [PendaftaranController::class, 'updateRiwayatPekerjaanByToken'])
    ->middleware('throttle:candidate-write')
    ->name('pendaftaran.api.token.riwayat-pekerjaan.update');

Route::get('/pendaftaran/api/token/{token}/riwayat-pekerjaan', function (string $token) {
    return redirect()->route('pendaftaran.show', ['token' => $token]);
})->name('pendaftaran.api.token.riwayat-pekerjaan.show');

/*
|--------------------------------------------------------------------------
| Step 5 - Kesiapan Bekerja
|--------------------------------------------------------------------------
*/
Route::patch('/pendaftaran/api/token/{token}/kesiapan-bekerja', [PendaftaranController::class, 'updateKesiapanBekerjaByToken'])
    ->middleware('throttle:candidate-write')
    ->name('pendaftaran.api.token.kesiapan-bekerja.update');

Route::get('/pendaftaran/api/token/{token}/kesiapan-bekerja', function (string $token) {
    return redirect()->route('pendaftaran.show', ['token' => $token]);
})->name('pendaftaran.api.token.kesiapan-bekerja.show');

/*
|--------------------------------------------------------------------------
| Halaman Pendaftaran Kandidat
|--------------------------------------------------------------------------
*/
Route::get('/pendaftaran', [PendaftaranController::class, 'index'])
    ->name('pendaftaran.index');

/*
|--------------------------------------------------------------------------
| Halaman Cek Tahapan Pelamar
|--------------------------------------------------------------------------
*/
Route::get('/pendaftaran/{token}/cek-tahapan', [CekTahapanPelamarController::class, 'show'])
    ->name('pendaftaran.cek-tahapan');

Route::get('/pendaftaran/{token}/tahapan', [CekTahapanPelamarController::class, 'show'])
    ->name('pendaftaran.tahapan');

/*
|--------------------------------------------------------------------------
| Route Token Pendaftaran
|--------------------------------------------------------------------------
| Route ini wajib paling bawah supaya tidak menangkap route lain.
|--------------------------------------------------------------------------
*/
Route::get('/pendaftaran/{token}', [PendaftaranController::class, 'show'])
    ->name('pendaftaran.show');
