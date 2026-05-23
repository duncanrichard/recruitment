<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

require __DIR__ . '/pendaftaran.php';
require __DIR__ . '/admin.php';
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