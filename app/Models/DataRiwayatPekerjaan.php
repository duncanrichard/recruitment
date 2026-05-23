<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DataRiwayatPekerjaan extends Model
{
    use SoftDeletes;

    protected $table = 'data_riwayat_pekerjaan';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'data_riwayat_diri_id',

        'nama_perusahaan',
        'posisi_pekerjaan_terakhir',
        'periode_kerja_awal',
        'periode_kerja_akhir',
        'gaji_terakhir',

        'status_pekerjaan',
        'posisi_pekerjaan',
        'bidang_pekerjaan',
        'lokasi_perusahaan',
        'tahun_mulai_bekerja',
        'tahun_selesai_bekerja',
        'lama_bekerja',
        'deskripsi_pekerjaan',
        'alasan_berhenti',
        'keahlian',
        'catatan_pekerjaan',

        'referensi_kerja',
        'nama_refrensi',
        'telp_refrensi',

        'refrensi_rekan_kerja',
        'nama_refrensi_rekan',
        'telp_refrensi_rekan',

        'refrensi_kerabat',
        'nama_refrensi_kerabat',
        'telp_refrensi_kerabat',

        'deleted_by',
    ];

    protected $casts = [
        'periode_kerja_awal' => 'date:Y-m-d',
        'periode_kerja_akhir' => 'date:Y-m-d',
        'gaji_terakhir' => 'decimal:2',

        'tahun_mulai_bekerja' => 'string',
        'tahun_selesai_bekerja' => 'string',
        'lama_bekerja' => 'string',

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

            $model->gaji_terakhir = self::normalizeGaji($model->gaji_terakhir);
            $model->tahun_mulai_bekerja = self::normalizeTahun($model->tahun_mulai_bekerja);
            $model->tahun_selesai_bekerja = self::normalizeTahun($model->tahun_selesai_bekerja);
            $model->lama_bekerja = self::normalizeLamaBekerja(
                $model->lama_bekerja,
                $model->tahun_mulai_bekerja,
                $model->tahun_selesai_bekerja
            );
        });

        static::saving(function (self $model) {
            $model->gaji_terakhir = self::normalizeGaji($model->gaji_terakhir);
            $model->tahun_mulai_bekerja = self::normalizeTahun($model->tahun_mulai_bekerja);
            $model->tahun_selesai_bekerja = self::normalizeTahun($model->tahun_selesai_bekerja);
            $model->lama_bekerja = self::normalizeLamaBekerja(
                $model->lama_bekerja,
                $model->tahun_mulai_bekerja,
                $model->tahun_selesai_bekerja
            );
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

    public static function normalizeTahun($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $cleanValue = preg_replace('/\D/', '', (string) $value);
        $cleanValue = substr($cleanValue, 0, 4);

        if (strlen($cleanValue) !== 4) {
            return null;
        }

        return $cleanValue;
    }

    public static function normalizeLamaBekerja($value, $tahunMulai = null, $tahunSelesai = null): ?string
    {
        $tahunMulai = self::normalizeTahun($tahunMulai);
        $tahunSelesai = self::normalizeTahun($tahunSelesai);

        if ($tahunMulai && $tahunSelesai) {
            $mulai = (int) $tahunMulai;
            $selesai = (int) $tahunSelesai;

            if ($selesai >= $mulai) {
                return (string) ($selesai - $mulai);
            }

            return null;
        }

        if ($value === null || $value === '') {
            return null;
        }

        $cleanValue = preg_replace('/[^0-9]/', '', (string) $value);

        return $cleanValue !== '' ? $cleanValue : null;
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