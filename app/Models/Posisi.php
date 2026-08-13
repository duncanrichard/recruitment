<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Posisi extends Model
{
    use HasUuids;

    protected $table = 'posisi';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'nama_posisi',
        'deskripsi',
        'str_aktif',
    ];

    protected $casts = [
        'id' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function uniqueIds(): array
    {
        return ['id'];
    }

    public function spesifikasi(): HasMany
    {
        return $this->hasMany(PosisiSpesifikasi::class)->orderBy('urutan');
    }
}
