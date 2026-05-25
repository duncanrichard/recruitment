<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DaftarHadirTestMmpi extends Model
{
    use SoftDeletes;

    protected $table = 'daftar_hadir_test_mmpi';

    protected $guarded = [];

    protected $casts = [
        'tanggal_kehadiran' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function jadwalTestMmpi()
    {
        return $this->belongsTo(JadwalTestMmpi::class, 'jadwal_test_mmpi_id');
    }

    public function dataRiwayatDiri()
    {
        return $this->belongsTo(DataRiwayatDiri::class, 'data_riwayat_diri_id');
    }
}
