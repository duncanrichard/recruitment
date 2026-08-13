<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
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
        'token_api_wa_ciphertext',
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
        'token_api_wa_ciphertext',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });

        static::saving(function (self $model) {
            if (! Schema::hasColumn($model->getTable(), 'token_api_wa_ciphertext')) {
                return;
            }

            $plainToken = $model->getAttributes()['token_api_wa'] ?? null;

            if (is_string($plainToken) && trim($plainToken) !== '') {
                $model->setAttribute('token_api_wa_ciphertext', Crypt::encryptString($plainToken));
                $model->setAttribute('token_api_wa', null);
            }
        });
    }

    protected function tokenApiWa(): Attribute
    {
        return Attribute::get(function (?string $value, array $attributes): ?string {
            if (! empty($value)) {
                return $value;
            }

            $ciphertext = $attributes['token_api_wa_ciphertext'] ?? null;

            return $ciphertext ? Crypt::decryptString($ciphertext) : null;
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
