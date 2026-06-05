<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DaftarHadirTestMmpi extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'daftar_hadir_test_mmpi';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    protected $casts = [
        'tanggal_kehadiran' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function jadwalTestMmpi(): BelongsTo
    {
        return $this->belongsTo(
            JadwalTestMmpi::class,
            'jadwal_test_mmpi_id',
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
}