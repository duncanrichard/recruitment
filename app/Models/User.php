<?php

namespace App\Models;

use App\Models\Divisi;
use App\Models\Role;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasUuids;
    use Notifiable;

    protected $table = 'users';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'remember_token',
        'role_id',
        'divisi_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'id' => 'string',
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'role_id' => 'integer',
        'divisi_id' => 'string',
    ];

    public function uniqueIds(): array
    {
        return ['id'];
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'divisi_id', 'id');
    }

    public function setPasswordAttribute($value): void
    {
        if (!empty($value)) {
            if (Hash::needsRehash($value)) {
                $this->attributes['password'] = Hash::make($value);
                return;
            }

            $this->attributes['password'] = $value;
        }
    }
}