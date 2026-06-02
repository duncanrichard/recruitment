<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PermintaanKandidatRecruitment extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'permintaan_kandidat_recruitments';

    protected $fillable = [
        'pt_membutuhkan',
        'divisi_departemen',
        'permintaan_oleh',
        'tanggal_permintaan',
        'deskripsi_permintaan',

        'nama_posisi_jabatan',
        'jumlah_karyawan',
        'lokasi_kerja',

        'tipe_pekerjaan',
        'jadwal_kerja',
        'deskripsi_pekerjaan',
        'gaji_benefit',

        'pendidikan_minimum',
        'usia',
        'jenis_kelamin',
        'pengalaman_kerja',
        'keterampilan_teknis',
        'keterampilan_interpersonal',
        'syarat_khusus',
        'keahlian_khusus',
        'sertifikat',

        'tanggal_mulai_diperlukan',
        'urgent_permintaan',
        'alasan_permintaan',

        'karakter_pribadi',
        'hasil_test_tertulis',
        'permintaan_khusus',
        'karakter_profesional',

        'proses_seleksi',
        'materi_ppt',
        'informasi_tambahan',
        'penyebaran_iklan',

        'status_permintaan',
    ];

    protected $casts = [
        'tanggal_permintaan' => 'date',
        'tanggal_mulai_diperlukan' => 'date',
        'jumlah_karyawan' => 'integer',
    ];
}