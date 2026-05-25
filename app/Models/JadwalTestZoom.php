<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class JadwalTestZoom extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'jadwal_test_zoom';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'data_riwayat_diri_id',
        'jadwal',
        'kehadiran',
        'hasil_test',
        'link_zoom',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'jadwal' => 'datetime',
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