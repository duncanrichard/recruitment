<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JadwalTestZoom extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'jadwal_test_zoom';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'data_riwayat_diri_id',

        'group_key',
        'sesi',

        'jadwal',
        'jadwal_mulai',
        'jadwal_selesai',

        'kehadiran',
        'hasil_test',

        'link_zoom',

        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'jadwal' => 'datetime',
        'jadwal_mulai' => 'datetime',
        'jadwal_selesai' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function dataRiwayatDiri(): BelongsTo
    {
        return $this->belongsTo(
            DataRiwayatDiri::class,
            'data_riwayat_diri_id',
            'id'
        );
    }

    public function pelamar(): BelongsTo
    {
        return $this->belongsTo(
            DataRiwayatDiri::class,
            'data_riwayat_diri_id',
            'id'
        );
    }

    public function daftarHadirTestZoom(): HasMany
    {
        return $this->hasMany(
            DaftarHadirTestZoom::class,
            'jadwal_test_zoom_id',
            'id'
        );
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by',
            'id'
        );
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by',
            'id'
        );
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'deleted_by',
            'id'
        );
    }
}
