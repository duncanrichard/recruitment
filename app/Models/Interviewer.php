<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Interviewer extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'interviewers';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'nama',
        'no_wa',
        'jabatan_id',
        'divisi_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function jabatan(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id', 'id');
    }

    public function divisi(): BelongsTo
    {
        return $this->belongsTo(Divisi::class, 'divisi_id', 'id');
    }

    public function jadwalInterviewPanelis(): HasMany
    {
        return $this->hasMany(
            JadwalInterviewPanelis::class,
            'interviewer_id',
            'id'
        );
    }

    public function jadwalInterviews(): BelongsToMany
    {
        return $this->belongsToMany(
            JadwalInterview::class,
            'jadwal_interview_panelis',
            'interviewer_id',
            'jadwal_interview_id'
        )
            ->withPivot([
                'id',
                'created_at',
            ]);
    }
}