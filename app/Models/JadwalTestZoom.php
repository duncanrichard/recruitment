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
        'link_zoom',
        'kehadiran',
    ];

    protected $casts = [
        'jadwal' => 'datetime',
    ];

    public function dataRiwayatDiri(): BelongsTo
    {
        return $this->belongsTo(
            DataRiwayatDiri::class,
            'data_riwayat_diri_id',
            'id'
        );
    }
}
