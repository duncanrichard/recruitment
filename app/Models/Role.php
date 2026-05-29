<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'nama_role',
        'kode_role',
        'keterangan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}