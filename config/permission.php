<?php

use App\Models\Permission;
use App\Models\Role;
use Spatie\Permission\DefaultTeamResolver;

return [

    /*
    |--------------------------------------------------------------------------
    | Permission Models
    |--------------------------------------------------------------------------
    */

    'models' => [

        /*
         * Model permission custom kamu.
         * Karena permissions.id memakai UUID, model ini harus extend
         * Spatie\Permission\Models\Permission dan memakai HasUuids.
         */
        'permission' => Permission::class,

        /*
         * Model role custom kamu.
         * Karena roles.id memakai UUID, model ini harus extend
         * Spatie\Permission\Models\Role dan memakai HasUuids.
         */
        'role' => Role::class,

        /*
         * Teams tidak dipakai.
         */
        'team' => null,

        /*
         * Default model untuk fitur HasModels.
         * Biarkan null supaya mengikuti model dari guard.
         */
        'default_model' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Permission Table Names
    |--------------------------------------------------------------------------
    */

    'table_names' => [

        'roles' => 'roles',

        'permissions' => 'permissions',

        'model_has_permissions' => 'model_has_permissions',

        'model_has_roles' => 'model_has_roles',

        'role_has_permissions' => 'role_has_permissions',
    ],

    /*
    |--------------------------------------------------------------------------
    | Permission Column Names
    |--------------------------------------------------------------------------
    */

    'column_names' => [

        /*
         * Karena migration kamu memakai default:
         * role_id dan permission_id,
         * biarkan null.
         */
        'role_pivot_key' => null,

        'permission_pivot_key' => null,

        /*
         * Karena tabel model_has_roles dan model_has_permissions
         * memakai kolom model_id uuid, tetap gunakan model_id.
         *
         * Kalau dulu migration kamu dibuat dengan model_uuid,
         * ubah ini menjadi: 'model_uuid'
         */
        'model_morph_key' => 'model_id',

        /*
         * Tidak dipakai karena teams false.
         */
        'team_foreign_key' => 'team_id',
    ],

    /*
    |--------------------------------------------------------------------------
    | Permission Check Method
    |--------------------------------------------------------------------------
    */

    'register_permission_check_method' => true,

    /*
    |--------------------------------------------------------------------------
    | Octane
    |--------------------------------------------------------------------------
    */

    'register_octane_reset_listener' => false,

    /*
    |--------------------------------------------------------------------------
    | Events
    |--------------------------------------------------------------------------
    */

    'events_enabled' => false,

    /*
    |--------------------------------------------------------------------------
    | Teams
    |--------------------------------------------------------------------------
    */

    'teams' => false,

    'team_resolver' => DefaultTeamResolver::class,

    /*
    |--------------------------------------------------------------------------
    | Passport Client Credentials
    |--------------------------------------------------------------------------
    */

    'use_passport_client_credentials' => false,

    /*
    |--------------------------------------------------------------------------
    | Exception Display
    |--------------------------------------------------------------------------
    */

    'display_permission_in_exception' => false,

    'display_role_in_exception' => false,

    /*
    |--------------------------------------------------------------------------
    | Wildcard Permission
    |--------------------------------------------------------------------------
    */

    'enable_wildcard_permission' => false,

    // 'wildcard_permission' => Spatie\Permission\WildcardPermission::class,

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    */

    'cache' => [

        'expiration_time' => DateInterval::createFromDateString('24 hours'),

        'key' => 'spatie.permission.cache',

        /*
         * Pakai default supaya mengikuti CACHE_STORE di .env.
         * Kalau CACHE_STORE=database, pastikan tabel cache ada.
         * Kalau tidak mau database cache, pakai CACHE_STORE=file di .env.
         */
        'store' => 'default',
    ],
];