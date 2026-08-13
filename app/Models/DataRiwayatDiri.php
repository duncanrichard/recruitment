<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
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

    protected $hidden = [
        'token_hash',
        'token_ciphertext',
    ];

    protected $fillable = [
        'id',
        'token',
        'token_hash',
        'token_ciphertext',
        'token_expires_at',
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
        'token_expires_at' => 'datetime',
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

            $plainToken = $model->getRawOriginal('token') ?: self::generateToken();

            if (
                Schema::hasColumn($model->getTable(), 'token_hash')
                && Schema::hasColumn($model->getTable(), 'token_ciphertext')
            ) {
                $model->setAttribute('token_hash', hash('sha256', $plainToken));
                $model->setAttribute('token_ciphertext', Crypt::encryptString($plainToken));
                $model->setAttribute('token', null);
            } else {
                $model->setAttribute('token', $plainToken);
            }

            if (
                Schema::hasColumn($model->getTable(), 'token_expires_at')
                && empty($model->token_expires_at)
            ) {
                $model->token_expires_at = now()->addDays(
                    (int) config('recruitment.candidate_token_lifetime_days', 90)
                );
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
            $token = 'KND-'.Str::random(64);
        } while (self::query()->where('token', $token)->exists());

        return $token;
    }

    public function scopeWithValidToken(Builder $query, string $token): Builder
    {
        if (Schema::hasColumn($this->getTable(), 'token_hash')) {
            $query->where(function (Builder $query) use ($token) {
                $query->where('token_hash', hash('sha256', $token))
                    ->orWhere('token', $token);
            });
        } else {
            $query->where('token', $token);
        }

        if (Schema::hasColumn($this->getTable(), 'token_expires_at')) {
            $query->where(function (Builder $query) {
                $query->whereNull('token_expires_at')
                    ->orWhere('token_expires_at', '>', now());
            });
        }

        return $query;
    }

    protected function token(): Attribute
    {
        return Attribute::get(function (?string $value, array $attributes): ?string {
            if (! empty($value)) {
                return $value;
            }

            $ciphertext = $attributes['token_ciphertext'] ?? null;

            return $ciphertext ? Crypt::decryptString($ciphertext) : null;
        });
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
