<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Pendidikan extends Model
{
    use SoftDeletes;

    protected $table = 'pendidikan';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'pendidikan',
        'created_by',
        'updated_by',
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
