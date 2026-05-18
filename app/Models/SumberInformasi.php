<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SumberInformasi extends Model
{
    protected $table = 'sumber_informasi';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'informasi',
        'deleted_by',
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
}