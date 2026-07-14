<?php

namespace App\Models;

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
        'updated_by',
        'deleted_by',
    ];

    /*
     * Token tidak ikut terkirim saat model diubah menjadi JSON.
     * Controller tetap dapat mengakses $model->token_api_wa.
     */
    protected $hidden = [
        'token_api_wa',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $model) {
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
