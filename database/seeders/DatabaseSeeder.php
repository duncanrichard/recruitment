<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

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
        | Seed Permission
        |--------------------------------------------------------------------------
        | PermissionSeeder akan:
        | - membuat semua permission
        | - membuat role Superadmin
        | - sync semua permission ke role Superadmin
        */
        $this->call([
            PermissionSeeder::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Create Default Superadmin User
        |--------------------------------------------------------------------------
        | Password akan otomatis di-hash oleh mutator setPasswordAttribute()
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
        | Assign Role Superadmin
        |--------------------------------------------------------------------------
        */
        $superadminRole = Role::query()
            ->where('name', 'Superadmin')
            ->where('guard_name', 'web')
            ->first();

        if ($superadminRole) {
            $superadminUser->syncRoles([$superadminRole]);
        }
    }
}