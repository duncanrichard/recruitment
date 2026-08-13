<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OfferingLetter extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'offering_letter';

    protected $fillable = [
        'data_riwayat_diri_id',
        'pengajuan_dirut_id',
        'jadwal_ol',
        'status_ol',
        'deleted_by',
    ];

    public function dataRiwayatDiri()
    {
        return $this->belongsTo(DataRiwayatDiri::class, 'data_riwayat_diri_id');
    }

    public function pengajuanDirut()
    {
        return $this->belongsTo(PengajuanDirut::class, 'pengajuan_dirut_id');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
