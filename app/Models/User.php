<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class User extends Authenticatable
{
    use Notifiable;
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'divisi_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function divisi()
    {
        return $this->belongsTo(Divisi::class);
    }
}