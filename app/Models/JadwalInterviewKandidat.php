<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class JadwalInterviewKandidat extends Model
{
    use HasUuids;

    protected $table = 'jadwal_interview_kandidat';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'jadwal_interview_id',
        'data_riwayat_diri_id',
        'status_kehadiran',
        'hasil_interview',
        'catatan',
    ];

    public function jadwalInterview()
    {
        return $this->belongsTo(JadwalInterview::class, 'jadwal_interview_id');
    }

    public function kandidat()
    {
        return $this->belongsTo(DataRiwayatDiri::class, 'data_riwayat_diri_id');
    }
}