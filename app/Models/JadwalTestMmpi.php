<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JadwalTestMmpi extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'jadwal_test_mmpi';

    protected $guarded = [];

    protected $casts = [
        'tanggal' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function daftarHadirTestZoom()
    {
        return $this->belongsTo(DaftarHadirTestZoom::class, 'daftar_hadir_test_zoom_id');
    }

    public function dataRiwayatDiri()
    {
        return $this->belongsTo(DataRiwayatDiri::class, 'data_riwayat_diri_id');
    }
}
