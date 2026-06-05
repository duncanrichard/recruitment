<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalOfferingLetter extends Model
{
    use HasUuids;

    protected $table = 'jadwal_offering_letters';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'hasil_review_management_id',
        'tanggal_ol',
        'jam_ol',
        'metode',
        'link',
        'pic',
        'catatan',
        'status_jadwal',
    ];

    public function hasilReviewManagement(): BelongsTo
    {
        return $this->belongsTo(
            HasilReviewManagement::class,
            'hasil_review_management_id',
            'id'
        );
    }
}