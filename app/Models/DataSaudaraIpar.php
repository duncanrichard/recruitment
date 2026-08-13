<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DataSaudaraIpar extends Model
{
    protected $table = 'data_saudara_ipar';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'data_riwayat_diri_id',
        'data_riwayat_keluarga_id',
        'nama_saudara_ipar',
        'pekerjaan',
        'jenis_kelamin',
        'hubungan',
        'no_hp',
        'alamat',
        'deleted_by',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function dataRiwayatKeluarga()
    {
        return $this->belongsTo(DataRiwayatKeluarga::class, 'data_riwayat_keluarga_id', 'id');
    }

    public function dataRiwayatDiri()
    {
        return $this->belongsTo(DataRiwayatDiri::class, 'data_riwayat_diri_id', 'id');
    }
}
