<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
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

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id');
    }

    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'divisi_id');
    }

    public function jadwalInterviews()
    {
        return $this->belongsToMany(
            JadwalInterview::class,
            'jadwal_interview_panelis',
            'interviewer_id',
            'jadwal_interview_id'
        );
    }
}
