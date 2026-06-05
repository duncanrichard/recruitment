<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JadwalInterview extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'jadwal_interview';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'judul_interview',
        'jadwal_interview',
        'deleted_by',
    ];

    protected $casts = [
        'jadwal_interview' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function panelis()
    {
        return $this->belongsToMany(
            Interviewer::class,
            'jadwal_interview_panelis',
            'jadwal_interview_id',
            'interviewer_id'
        );
    }

    public function jadwalInterviewKandidat(): HasMany
    {
        return $this->hasMany(
            JadwalInterviewKandidat::class,
            'jadwal_interview_id',
            'id'
        );
    }
}