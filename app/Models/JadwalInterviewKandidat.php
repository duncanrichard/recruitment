<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JadwalInterviewKandidat extends Model
{
    use HasUuids;

    protected $table = 'jadwal_interview_kandidat';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'jadwal_interview_id',
        'data_riwayat_diri_id',
        'status_kehadiran',
        'hasil_interview',
        'catatan',
        'file_cv',
        'file_foto',
    ];

    protected static function booted(): void
    {
        static::saved(function (JadwalInterviewKandidat $model) {
            if (! $model->wasChanged('hasil_interview')) {
                return;
            }

            $hasilInterview = trim((string) $model->hasil_interview);

            if (in_array($hasilInterview, ['Lolos Interview', 'Dipertimbangkan'], true)) {
                $model->reviewManagement()->firstOrCreate([
                    'hasil_interview_id' => $model->id,
                ], [
                    'review_management' => null,
                    'status' => null,
                ]);

                return;
            }

            if (
                $hasilInterview === '' ||
                $hasilInterview === 'Tidak Lolos Interview'
            ) {
                $model->reviewManagement()->delete();
            }
        });

        static::deleting(function (JadwalInterviewKandidat $model) {
            $model->reviewManagement()->delete();
        });
    }

    public function jadwalInterview(): BelongsTo
    {
        return $this->belongsTo(
            JadwalInterview::class,
            'jadwal_interview_id',
            'id'
        );
    }

    public function kandidat(): BelongsTo
    {
        return $this->belongsTo(
            DataRiwayatDiri::class,
            'data_riwayat_diri_id',
            'id'
        );
    }

    public function reviewManagement(): HasOne
    {
        return $this->hasOne(
            HasilReviewManagement::class,
            'hasil_interview_id',
            'id'
        );
    }
}
