<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $superadminRole = Role::query()->firstOrCreate(
            [
                'name' => 'Superadmin',
                'guard_name' => 'web',
            ],
            [
                'id' => (string) Str::uuid(),
            ]
        );

        $user = User::query()->where('email', 'richardpratama9898@gmail.com')->first();

        if (! $user) {
            $user = User::query()->create([
                'uuid' => (string) Str::uuid(),
                'name' => 'Superadmin',
                'email' => 'richardpratama9898@gmail.com',
                'password' => 'password',
                'email_verified_at' => now(),
            ]);
        } else {
            if (empty($user->uuid)) {
                $user->uuid = (string) Str::uuid();
            }

            $user->name = 'Superadmin';
            $user->password = 'password';
            $user->email_verified_at = $user->email_verified_at ?: now();
            $user->save();
        }

        $user->syncRoles([$superadminRole]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}