<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Posisi extends Model
{
    protected $table = 'posisi';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'nama_posisi',
        'deskripsi',
        'str_aktif',
        'deleted_by',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }

            if (empty($model->str_aktif)) {
                $model->str_aktif = 'active';
            }
        });
    }
}