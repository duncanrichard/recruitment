<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DaftarHadirTestZoom extends Model
{
    use SoftDeletes;

    protected $table = 'daftar_hadir_test_zoom';

    protected $guarded = [];

    protected $casts = [
        'tanggal_kehadiran' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function dataRiwayatDiri()
    {
        return $this->belongsTo(DataRiwayatDiri::class, 'data_riwayat_diri_id');
    }

    public function jadwalTestZoom()
    {
        return $this->belongsTo(JadwalTestZoom::class, 'jadwal_test_zoom_id');
    }
}
