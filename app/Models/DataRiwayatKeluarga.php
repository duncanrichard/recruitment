<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DataRiwayatKeluarga extends Model
{
    protected $table = 'data_riwayat_keluarga';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'data_riwayat_diri_id',

        // Data orang tua
        'nama_ayah',
        'no_hp_ayah',
        'alamat_ayah',

        'nama_ibu',
        'no_hp_ibu',
        'alamat_ibu',

        // Pasangan
        'nama_suami_istri',
        'pekerjaan_suami_istri',
        'tlpn_suami_istri',

        // Mertua
        'nama_bapak_mertua',
        'pekerjaan_bapak_mertua',
        'nama_ibu_mertua',
        'pekerjaan_ibu_mertua',

        // Hubungan kerabat
        'kerabat_bekerja_diinstansi',
        'hubungan_kerabat_instansi',

        // Kontak darurat
        'tlpn_darurat',
        'kontak_darurat',

        'deleted_by',
    ];

    protected $casts = [
        'hubungan_kerabat_instansi' => 'array',
        'kontak_darurat' => 'array',
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

    public function dataRiwayatDiri()
    {
        return $this->belongsTo(DataRiwayatDiri::class, 'data_riwayat_diri_id', 'id');
    }

    public function saudaraKandung()
    {
        return $this->hasMany(DataSaudaraKandung::class, 'data_riwayat_keluarga_id', 'id');
    }

    public function saudaraIpar()
    {
        return $this->hasMany(DataSaudaraIpar::class, 'data_riwayat_keluarga_id', 'id');
    }
}