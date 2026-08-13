<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class HasilReviewManagement extends Model
{
    use HasUuids;

    protected $table = 'hasil_review_management';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'hasil_interview_id',
        'review_management',
        'status',
    ];

    public function hasilInterview(): BelongsTo
    {
        return $this->belongsTo(
            JadwalInterviewKandidat::class,
            'hasil_interview_id',
            'id'
        );
    }

    public function jadwalOfferingLetter(): HasOne
    {
        return $this->hasOne(
            JadwalOfferingLetter::class,
            'hasil_review_management_id',
            'id'
        );
    }
}
