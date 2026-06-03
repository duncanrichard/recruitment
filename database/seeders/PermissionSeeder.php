<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guardName = 'web';

        $modules = [
            /*
            |--------------------------------------------------------------------------
            | Master Data
            |--------------------------------------------------------------------------
            */
            [
                'group' => 'Master Data',
                'label' => 'Posisi',
                'key' => 'admin.master-data.posisi',
                'actions' => [
                    'list' => 'View',
                    'store' => 'Create',
                    'update' => 'Update',
                    'destroy' => 'Delete',
                ],
            ],
            [
                'group' => 'Master Data',
                'label' => 'Jabatan',
                'key' => 'admin.master-data.jabatan',
                'actions' => [
                    'list' => 'View',
                    'store' => 'Create',
                    'update' => 'Update',
                    'destroy' => 'Delete',
                ],
            ],
            [
                'group' => 'Master Data',
                'label' => 'Divisi',
                'key' => 'admin.master-data.divisi',
                'actions' => [
                    'list' => 'View',
                    'store' => 'Create',
                    'update' => 'Update',
                    'destroy' => 'Delete',
                ],
            ],
            [
                'group' => 'Master Data',
                'label' => 'Pendidikan',
                'key' => 'admin.master-data.pendidikan',
                'actions' => [
                    'list' => 'View',
                    'store' => 'Create',
                    'update' => 'Update',
                    'destroy' => 'Delete',
                ],
            ],

            /*
            |--------------------------------------------------------------------------
            | Account
            |--------------------------------------------------------------------------
            */
            [
                'group' => 'Account',
                'label' => 'Role',
                'key' => 'admin.account.role',
                'actions' => [
                    'list' => 'View',
                    'store' => 'Create',
                    'update' => 'Update',
                    'destroy' => 'Delete',
                ],
            ],
            [
                'group' => 'Account',
                'label' => 'User',
                'key' => 'admin.account.user',
                'actions' => [
                    'list' => 'View',
                    'store' => 'Create',
                    'update' => 'Update',
                    'destroy' => 'Delete',
                ],
            ],
            [
                'group' => 'Account',
                'label' => 'Permission',
                'key' => 'admin.account.permission',
                'actions' => [
                    'list' => 'View',
                    'store' => 'Create',
                    'update' => 'Update',
                    'destroy' => 'Delete',
                    'setting' => 'Setting',
                ],
            ],
        ];

        foreach ($modules as $module) {
            foreach ($module['actions'] as $actionKey => $actionLabel) {
                Permission::firstOrCreate([
                    'name' => $module['key'] . '.' . $actionKey,
                    'guard_name' => $guardName,
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Superadmin Full Access
        |--------------------------------------------------------------------------
        */
        $superadmin = Role::firstOrCreate([
            'name' => 'Superadmin',
            'guard_name' => $guardName,
        ]);

        $superadmin->syncPermissions(
            Permission::query()
                ->where('guard_name', $guardName)
                ->get()
        );

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}