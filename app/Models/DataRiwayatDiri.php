<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DataRiwayatDiri extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'data_riwayat_diri';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
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
        'tempat_lahir',
        'jenis_kelamin',
        'alamat_ktp',
        'alamat_domisili',
        'alamat',
        'provinsi_id',
        'kabupaten_id',
        'kecamatan_id',
        'kelurahan_id',
        'rt',
        'rw',
        'kewarganegaraan_id',
        'status_pernikahan_id',
        'no_wa',
        'sosial_media_id',
        'sumber_informasi_id',
        'gol_darah',
        'tinggi_badan',
        'berat_badan',
        'str_aktif',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'id' => 'string',
        'posisi_yang_dilamar' => 'string',
        'perusahaan_dilamar' => 'string',
        'pendidikan_id' => 'string',
        'agama_id' => 'string',
        'tanggal_lahir' => 'date',
        'tanggal_skrining' => 'date',
        'provinsi_id' => 'string',
        'kabupaten_id' => 'string',
        'kecamatan_id' => 'string',
        'kelurahan_id' => 'string',
        'kewarganegaraan_id' => 'string',
        'status_pernikahan_id' => 'string',
        'sosial_media_id' => 'string',
        'sumber_informasi_id' => 'string',
        'tinggi_badan' => 'integer',
        'berat_badan' => 'integer',
        'created_by' => 'string',
        'updated_by' => 'string',
        'deleted_by' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function uniqueIds(): array
    {
        return ['id'];
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }

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

            if (
                Schema::hasColumn($model->getTable(), 'created_by')
                && empty($model->created_by)
                && Auth::check()
            ) {
                $model->created_by = Auth::user()?->uuid;
            }
        });

        static::updating(function ($model) {
            if (
                Schema::hasColumn($model->getTable(), 'updated_by')
                && Auth::check()
            ) {
                $model->updated_by = Auth::user()?->uuid;
            }
        });

        static::deleting(function ($model) {
            if (
                ! $model->isForceDeleting()
                && Schema::hasColumn($model->getTable(), 'deleted_by')
                && Auth::check()
            ) {
                $model->deleted_by = Auth::user()?->uuid;
                $model->saveQuietly();
            }
        });
    }

    public static function generateToken(): string
    {
        do {
            $token = 'KND-'
                . now()->format('Ymd-His')
                . '-'
                . strtoupper(Str::random(6));
        } while (self::query()->where('token', $token)->exists());

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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'uuid');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'uuid');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'uuid');
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by', 'uuid');
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

    public function sumberInformasi(): BelongsTo
    {
        return $this->belongsTo(SumberInformasi::class, 'sumber_informasi_id', 'id');
    }

    public function provinsi(): BelongsTo
    {
        return $this->belongsTo(Provinsi::class, 'provinsi_id', 'id');
    }

    public function kabupaten(): BelongsTo
    {
        return $this->belongsTo(Kabupaten::class, 'kabupaten_id', 'id');
    }

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_id', 'id');
    }

    public function kelurahan(): BelongsTo
    {
        return $this->belongsTo(Kelurahan::class, 'kelurahan_id', 'id');
    }

    public function sosialMedia(): HasMany
    {
        return $this->hasMany(SosialMedia::class, 'data_riwayat_diri_id', 'id');
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

    public function riwayatPekerjaan(): HasMany
    {
        return $this->hasMany(DataRiwayatPekerjaan::class, 'data_riwayat_diri_id', 'id');
    }

    public function kesiapanBekerja(): HasOne
    {
        return $this->hasOne(DataKesiapanBekerja::class, 'data_riwayat_diri_id', 'id');
    }

    /**
     * Jadwal Zoom terbaru.
     *
     * Jangan gunakan latestOfMany() atau ofMany() karena primary key `id`
     * bertipe UUID dan PostgreSQL tidak mendukung fungsi MAX(uuid).
     */
    public function jadwalTestZoom(): HasOne
    {
        return $this->hasOne(
            JadwalTestZoom::class,
            'data_riwayat_diri_id',
            'id'
        )->orderByDesc('jadwal');
    }

    public function jadwalTestZooms(): HasMany
    {
        return $this->hasMany(
            JadwalTestZoom::class,
            'data_riwayat_diri_id',
            'id'
        )->orderByDesc('jadwal');
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
