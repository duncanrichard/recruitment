<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PosisiSpesifikasi extends Model
{
    use HasUuids;

    protected $table = 'posisi_spesifikasi';
    protected $fillable = ['posisi_id', 'spesifikasi', 'urutan'];
}
