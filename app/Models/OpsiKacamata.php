<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class OpsiKacamata extends Model
{
    use SoftDeletes;

    protected $table = 'opsi_kacamata';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'opsi',
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

    public function riwayatKesehatan()
    {
        return $this->hasMany(DataRiwayatKesehatan::class, 'opsi_kacamata_id', 'id');
    }
}
