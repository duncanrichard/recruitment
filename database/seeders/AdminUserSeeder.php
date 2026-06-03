<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $superadminRole = Role::firstOrCreate(
            [
                'name' => 'Superadmin',
                'guard_name' => 'web',
            ]
        );

        $user = User::updateOrCreate(
            [
                'email' => 'richardpratama9898@gmail.com',
            ],
            [
                'name' => 'Superadmin',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $user->syncRoles([$superadminRole]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}