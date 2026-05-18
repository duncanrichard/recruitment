<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DataRiwayatDiri extends Model
{
    protected $table = 'data_riwayat_diri';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'token',
        'posisi_yang_dilamar',
        'perusahaan_dilamar',
        'nama_lengkap',
        'nama_panggil',
        'email',
        'pendidikan_id',
        'jurusan',
        'nama_institusi',
        'agama_id',
        'tanggal_lahir',
        'tanggal_skrining',
        'alamat_ktp',
        'alamat_domisili',
        'kewarganegaraan_id',
        'status_pernikahan_id',
        'no_wa',
        'sosial_media_id',
        'sumber_informasi_id',
        'gol_darah',
        'tinggi_badan',
        'berat_badan',
        'str_aktif',
        'deleted_by',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_skrining' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }

            if (empty($model->token)) {
                $model->token = self::generateToken();
            }
        });
    }

    public static function generateToken(): string
    {
        do {
            $token = 'KND-' . now()->format('Ymd-His') . '-' . strtoupper(Str::random(6));
        } while (self::where('token', $token)->exists());

        return $token;
    }

    public function posisi()
    {
        return $this->belongsTo(Posisi::class, 'posisi_yang_dilamar', 'id');
    }

    public function perusahaan()
    {
        return $this->belongsTo(DataPerusahaan::class, 'perusahaan_dilamar', 'id');
    }

    public function pendidikan()
    {
        return $this->belongsTo(Pendidikan::class, 'pendidikan_id', 'id');
    }

    public function agama()
    {
        return $this->belongsTo(Agama::class, 'agama_id', 'id');
    }

    public function kewarganegaraan()
    {
        return $this->belongsTo(Kewarganegaraan::class, 'kewarganegaraan_id', 'id');
    }

    public function statusPernikahan()
    {
        return $this->belongsTo(StatusPernikahan::class, 'status_pernikahan_id', 'id');
    }

    public function sosialMedia()
    {
        return $this->belongsTo(SosialMedia::class, 'sosial_media_id', 'id');
    }

    public function sumberInformasi()
    {
        return $this->belongsTo(SumberInformasi::class, 'sumber_informasi_id', 'id');
    }
}