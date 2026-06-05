<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class DataRiwayatDiri extends Model
{
    protected $table = 'data_riwayat_diri';

    protected $primaryKey = 'id';

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
        'provinsi_id',
        'kabupaten_id',
        'kecamatan_id',
        'kelurahan_id',
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

    public function posisi(): BelongsTo
    {
        return $this->belongsTo(Posisi::class, 'posisi_yang_dilamar', 'id');
    }

    public function perusahaan(): BelongsTo
    {
        return $this->belongsTo(DataPerusahaan::class, 'perusahaan_dilamar', 'id');
    }

    public function pendidikan(): BelongsTo
    {
        return $this->belongsTo(Pendidikan::class, 'pendidikan_id', 'id');
    }

    public function agama(): BelongsTo
    {
        return $this->belongsTo(Agama::class, 'agama_id', 'id');
    }

    public function kewarganegaraan(): BelongsTo
    {
        return $this->belongsTo(Kewarganegaraan::class, 'kewarganegaraan_id', 'id');
    }

    public function statusPernikahan(): BelongsTo
    {
        return $this->belongsTo(StatusPernikahan::class, 'status_pernikahan_id', 'id');
    }

    public function sosialMedia(): HasMany
    {
        return $this->hasMany(SosialMedia::class, 'data_riwayat_diri_id', 'id');
    }

    public function sumberInformasi(): BelongsTo
    {
        return $this->belongsTo(SumberInformasi::class, 'sumber_informasi_id', 'id');
    }

    public function riwayatKeluarga(): HasOne
    {
        return $this->hasOne(DataRiwayatKeluarga::class, 'data_riwayat_diri_id', 'id');
    }

    public function saudaraKandung(): HasMany
    {
        return $this->hasMany(DataSaudaraKandung::class, 'data_riwayat_diri_id', 'id');
    }

    public function saudaraIpar(): HasMany
    {
        return $this->hasMany(DataSaudaraIpar::class, 'data_riwayat_diri_id', 'id');
    }

    public function riwayatKesehatan(): HasOne
    {
        return $this->hasOne(DataRiwayatKesehatan::class, 'data_riwayat_diri_id', 'id');
    }

    public function riwayatPekerjaan(): HasOne
    {
        return $this->hasOne(DataRiwayatPekerjaan::class, 'data_riwayat_diri_id', 'id');
    }

    public function kesiapanBekerja(): HasOne
    {
        return $this->hasOne(DataKesiapanBekerja::class, 'data_riwayat_diri_id', 'id');
    }

    public function jadwalTestZoom(): HasOne
    {
        return $this->hasOne(JadwalTestZoom::class, 'data_riwayat_diri_id', 'id')
            ->orderByDesc('jadwal');
    }

    public function jadwalTestZooms(): HasMany
    {
        return $this->hasMany(JadwalTestZoom::class, 'data_riwayat_diri_id', 'id')
            ->orderByDesc('jadwal');
    }

    public function jadwalInterviewKandidat(): HasMany
    {
        return $this->hasMany(
            JadwalInterviewKandidat::class,
            'data_riwayat_diri_id',
            'id'
        );
    }
}