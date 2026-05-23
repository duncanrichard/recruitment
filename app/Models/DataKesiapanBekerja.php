<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DataKesiapanBekerja extends Model
{
    use SoftDeletes;

    protected $table = 'data_kesiapan_bekerja';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'data_riwayat_diri_id',

        'kapan_siap_bekerja',
        'ekpetasi_gaji',
        'penempatan',
        'proses_bkhang',
        'dapat_dipertanggung_jawabkan',
        'bersedia_training',

        'deleted_by',
    ];

    protected $casts = [
        'id' => 'string',
        'data_riwayat_diri_id' => 'string',

        'kapan_siap_bekerja' => 'string',
        'ekpetasi_gaji' => 'decimal:2',
        'penempatan' => 'string',
        'proses_bkhang' => 'string',
        'dapat_dipertanggung_jawabkan' => 'string',
        'bersedia_training' => 'string',

        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }

            $model->ekpetasi_gaji = self::normalizeGaji($model->ekpetasi_gaji);
            $model->penempatan = self::normalizePenempatan($model->penempatan);
        });

        static::saving(function (self $model) {
            $model->ekpetasi_gaji = self::normalizeGaji($model->ekpetasi_gaji);
            $model->penempatan = self::normalizePenempatan($model->penempatan);
        });
    }

    public static function normalizeGaji($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $cleanValue = preg_replace('/[^0-9.]/', '', (string) $value);

        if ($cleanValue === '' || !is_numeric($cleanValue)) {
            return null;
        }

        $maxValue = 999999999999999999.99;
        $numericValue = (float) $cleanValue;

        if ($numericValue > $maxValue) {
            $numericValue = $maxValue;
        }

        return number_format($numericValue, 2, '.', '');
    }

    public static function normalizePenempatan($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            return implode(', ', array_filter($value));
        }

        $stringValue = trim((string) $value);

        if ($stringValue === '') {
            return null;
        }

        return $stringValue;
    }

    public function dataRiwayatDiri()
    {
        return $this->belongsTo(DataRiwayatDiri::class, 'data_riwayat_diri_id', 'id');
    }

    public function pelamar()
    {
        return $this->belongsTo(DataRiwayatDiri::class, 'data_riwayat_diri_id', 'id');
    }
}