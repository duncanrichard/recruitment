<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalInterviewPanelis extends Model
{
    protected $table = 'jadwal_interview_panelis';

    protected $fillable = [
        'jadwal_interview_id',
        'interviewer_id',
    ];

    public function jadwalInterview()
    {
        return $this->belongsTo(JadwalInterview::class, 'jadwal_interview_id');
    }

    public function interviewer()
    {
        return $this->belongsTo(Interviewer::class, 'interviewer_id');
    }
}
