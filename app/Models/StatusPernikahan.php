<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class StatusPernikahan extends Model
{
    use SoftDeletes;

    protected $table = 'status_pernikahan';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'status_pernikahan',
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
