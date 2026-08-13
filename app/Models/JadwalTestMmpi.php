<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JadwalTestMmpi extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'jadwal_test_mmpi';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    protected $casts = [
        'jadwal' => 'datetime',
        'jadwal_mulai' => 'datetime',
        'jadwal_selesai' => 'datetime',
        'tanggal' => 'datetime',
        'deleted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function daftarHadirTestZoom(): BelongsTo
    {
        return $this->belongsTo(
            DaftarHadirTestZoom::class,
            'daftar_hadir_test_zoom_id',
            'id'
        );
    }

    public function dataRiwayatDiri(): BelongsTo
    {
        return $this->belongsTo(
            DataRiwayatDiri::class,
            'data_riwayat_diri_id',
            'id'
        );
    }

    public function daftarHadirTestMmpi(): HasMany
    {
        return $this->hasMany(
            DaftarHadirTestMmpi::class,
            'jadwal_test_mmpi_id',
            'id'
        );
    }
}
