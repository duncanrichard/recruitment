<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DataRiwayatKesehatan extends Model
{
    use SoftDeletes;

    protected $table = 'data_riwayat_kesehatan';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'data_riwayat_diri_id',
        'buta_warna',
        'opsi_kacamata_id',
        'alat_bantu_dengar',
        'menulis_dengan_tangan',
        'sering_gemetar',
        'tangan_sering_berkeringat',
        'penyakit_menular',
        'program_kehamilan',
        'punya_alergi',
        'nama_alergi',
        'punya_penyakit_genetik',
        'nama_penyakit',
        'riwayat_kronis',
        'pengobatan_psikolog',
        'kapan_dilakukan',
        'pernah_kecelakaan',
        'bagian_tubuh_kecelakaan',
        'pernah_operasi',
        'diagnosa_dokter',
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

    public function dataRiwayatDiri()
    {
        return $this->belongsTo(DataRiwayatDiri::class, 'data_riwayat_diri_id', 'id');
    }

    public function opsiKacamata()
    {
        return $this->belongsTo(OpsiKacamata::class, 'opsi_kacamata_id', 'id');
    }
}