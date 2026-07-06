<?php

namespace App\Models;

use App\Models\DataPerusahaan;
use App\Models\Divisi;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasUuids;
    use Notifiable;
    use HasRoles;

    protected $table = 'users';

    protected $primaryKey = 'uuid';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guard_name = 'web';

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'email_verified_at',
        'password',
        'remember_token',
        'divisi_id',
        'perusahaan_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'uuid' => 'string',
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'divisi_id' => 'string',
        'perusahaan_id' => 'string',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function guardName(): string
    {
        return 'web';
    }

    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'divisi_id', 'id');
    }

    public function perusahaans()
    {
        return $this->belongsToMany(
            DataPerusahaan::class,
            'data_perusahaan_user',
            'user_id',
            'perusahaan_id',
            'uuid',
            'id'
        )->withTimestamps();
    }

    public function setPasswordAttribute($value): void
    {
        if (! empty($value)) {
            if (Hash::needsRehash($value)) {
                $this->attributes['password'] = Hash::make($value);
                return;
            }

            $this->attributes['password'] = $value;
        }
    }
}