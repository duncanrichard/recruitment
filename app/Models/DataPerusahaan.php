<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DataPerusahaan extends Model
{
    use SoftDeletes;

    protected $table = 'data_perusahaan';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'kode',
        'nama_perusahaan',
        'no_wa',
        'token_api_wa',
        'created_by',
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

    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'data_perusahaan_user',
            'perusahaan_id',
            'user_id'
        )->withTimestamps();
    }
}