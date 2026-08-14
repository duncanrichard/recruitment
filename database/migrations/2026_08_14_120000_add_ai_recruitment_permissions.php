<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = collect([
            'admin.ai-recruitment.list',
            'admin.ai-recruitment.analyze',
        ])->map(fn (string $name) => Permission::firstOrCreate([
            'name' => $name,
            'guard_name' => 'web',
        ]));

        Role::query()
            ->whereIn('name', ['Superadmin', 'superadmin', 'Super Admin', 'super admin'])
            ->get()
            ->each(fn (Role $role) => $role->givePermissionTo($permissions));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', [
                'admin.ai-recruitment.list',
                'admin.ai-recruitment.analyze',
            ])
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
