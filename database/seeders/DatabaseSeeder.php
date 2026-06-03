<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Clear Spatie Permission Cache
        |--------------------------------------------------------------------------
        */
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        /*
        |--------------------------------------------------------------------------
        | Seed Permissions
        |--------------------------------------------------------------------------
        | PermissionSeeder akan membuat:
        | - semua permission
        | - role Superadmin
        | - sync semua permission ke role Superadmin
        */
        $this->call([
            PermissionSeeder::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Create / Update Default Superadmin User
        |--------------------------------------------------------------------------
        | Password otomatis di-hash oleh mutator setPasswordAttribute()
        | di model User kamu.
        */
        $superadminUser = User::updateOrCreate(
            [
                'email' => 'richardpratama9898@gmail.com',
            ],
            [
                'name' => 'Superadmin',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Create / Get Superadmin Role
        |--------------------------------------------------------------------------
        */
        $superadminRole = Role::firstOrCreate(
            [
                'name' => 'Superadmin',
                'guard_name' => 'web',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Assign Role Superadmin To User
        |--------------------------------------------------------------------------
        */
        $superadminUser->syncRoles([$superadminRole]);

        /*
        |--------------------------------------------------------------------------
        | Sync All Permissions To Superadmin Role
        |--------------------------------------------------------------------------
        */
        $superadminRole->syncPermissions(
            \App\Models\Permission::query()
                ->where('guard_name', 'web')
                ->get()
        );

        /*
        |--------------------------------------------------------------------------
        | Clear Cache Again
        |--------------------------------------------------------------------------
        */
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}