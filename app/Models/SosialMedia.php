<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SosialMedia extends Model
{
    use SoftDeletes;

    protected $table = 'sosial_media';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'data_riwayat_diri_id',
        'platform',
        'account',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function dataRiwayatDiri()
    {
        return $this->belongsTo(DataRiwayatDiri::class, 'data_riwayat_diri_id');
    }
}