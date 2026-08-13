<?php

namespace App\Http\Controllers\Admin\Account;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\PermissionRegistrar;

class PermissionController extends Controller
{
    public function index()
    {
        return view('pages.admin.index');
    }

    public function list()
    {
        try {
            $permissions = Permission::query()
                ->orderBy('name')
                ->get()
                ->map(function ($permission) {
                    $meta = $this->parsePermissionName($permission->name);

                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'guard_name' => $permission->guard_name,
                        'group' => $meta['group'],
                        'module' => $meta['module'],
                        'action' => $meta['action'],
                        'action_label' => $meta['action_label'],
                        'created_at' => $permission->created_at,
                        'updated_at' => $permission->updated_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Data permission berhasil diambil.',
                'data' => $permissions,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data permission.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function menu()
    {
        try {
            $roles = Role::query()
                ->withCount('permissions')
                ->orderBy('name')
                ->get()
                ->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'guard_name' => $role->guard_name,
                        'permissions_count' => $role->permissions_count,
                        'is_superadmin' => $this->isSuperadminRole($role),
                    ];
                });

            $permissions = Permission::query()
                ->orderBy('name')
                ->get();

            $menus = $permissions
                ->map(function ($permission) {
                    $meta = $this->parsePermissionName($permission->name);

                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'guard_name' => $permission->guard_name,
                        'group' => $meta['group'],
                        'module' => $meta['module'],
                        'action' => $meta['action'],
                        'action_label' => $meta['action_label'],
                    ];
                })
                ->groupBy('group')
                ->map(function ($groupItems, $groupName) {
                    return [
                        'group' => $groupName,
                        'modules' => $groupItems
                            ->groupBy('module')
                            ->map(function ($moduleItems, $moduleName) {
                                return [
                                    'module' => $moduleName,
                                    'permissions' => $moduleItems
                                        ->sortBy(function ($item) {
                                            return $this->actionSortOrder($item['action_label']);
                                        })
                                        ->values()
                                        ->all(),
                                ];
                            })
                            ->values()
                            ->all(),
                    ];
                })
                ->values()
                ->all();

            return response()->json([
                'success' => true,
                'message' => 'Menu permission berhasil diambil.',
                'data' => [
                    'roles' => $roles,
                    'menus' => $menus,
                ],
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil menu permission.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function rolePermissions(Role $role)
    {
        try {
            if ($this->isSuperadminRole($role)) {
                $permissionIds = Permission::query()
                    ->where('guard_name', $role->guard_name)
                    ->pluck('id')
                    ->map(fn ($id) => (string) $id)
                    ->values();
            } else {
                $permissionIds = $role->permissions()
                    ->pluck('permissions.id')
                    ->map(fn ($id) => (string) $id)
                    ->values();
            }

            return response()->json([
                'success' => true,
                'message' => 'Permission role berhasil diambil.',
                'data' => [
                    'role' => [
                        'id' => $role->id,
                        'name' => $role->name,
                        'guard_name' => $role->guard_name,
                        'is_superadmin' => $this->isSuperadminRole($role),
                    ],
                    'permission_ids' => $permissionIds,
                ],
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil permission role.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function syncRolePermissions(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['uuid', 'exists:permissions,id'],
        ]);

        try {
            DB::transaction(function () use ($validated, $role) {
                if ($this->isSuperadminRole($role)) {
                    $permissions = Permission::query()
                        ->where('guard_name', $role->guard_name)
                        ->get();

                    $role->syncPermissions($permissions);
                } else {
                    $permissions = Permission::query()
                        ->whereIn('id', $validated['permission_ids'] ?? [])
                        ->where('guard_name', $role->guard_name)
                        ->get();

                    $role->syncPermissions($permissions);
                }

                app(PermissionRegistrar::class)->forgetCachedPermissions();
            });

            return response()->json([
                'success' => true,
                'message' => 'Permission role berhasil disimpan.',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Permission role gagal disimpan.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:191',
                Rule::unique('permissions', 'name')
                    ->where('guard_name', $request->input('guard_name', 'web')),
            ],
            'guard_name' => ['nullable', 'string', 'max:100'],
        ]);

        try {
            $permission = DB::transaction(function () use ($validated) {
                $permission = Permission::create([
                    'name' => $validated['name'],
                    'guard_name' => $validated['guard_name'] ?? 'web',
                ]);

                $this->syncSuperadminAllPermissions();

                app(PermissionRegistrar::class)->forgetCachedPermissions();

                return $permission;
            });

            return response()->json([
                'success' => true,
                'message' => 'Permission berhasil ditambahkan.',
                'data' => $permission,
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Permission gagal ditambahkan.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:191',
                Rule::unique('permissions', 'name')
                    ->where('guard_name', $request->input('guard_name', $permission->guard_name))
                    ->ignore($permission->id, 'id'),
            ],
            'guard_name' => ['nullable', 'string', 'max:100'],
        ]);

        try {
            DB::transaction(function () use ($validated, $permission) {
                $permission->update([
                    'name' => $validated['name'],
                    'guard_name' => $validated['guard_name'] ?? $permission->guard_name ?? 'web',
                ]);

                $this->syncSuperadminAllPermissions();

                app(PermissionRegistrar::class)->forgetCachedPermissions();
            });

            return response()->json([
                'success' => true,
                'message' => 'Permission berhasil diperbarui.',
                'data' => $permission->fresh(),
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Permission gagal diperbarui.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function destroy(Permission $permission)
    {
        try {
            DB::transaction(function () use ($permission) {
                $permission->delete();

                $this->syncSuperadminAllPermissions();

                app(PermissionRegistrar::class)->forgetCachedPermissions();
            });

            return response()->json([
                'success' => true,
                'message' => 'Permission berhasil dihapus.',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Permission gagal dihapus.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function syncSuperadmin()
    {
        try {
            $this->syncSuperadminAllPermissions();

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            return response()->json([
                'success' => true,
                'message' => 'Permission Superadmin berhasil disinkronkan.',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Permission Superadmin gagal disinkronkan.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    private function syncSuperadminAllPermissions(): void
    {
        $roles = Role::query()
            ->whereRaw('LOWER(name) IN (?, ?)', ['superadmin', 'super admin'])
            ->get();

        foreach ($roles as $role) {
            $permissions = Permission::query()
                ->where('guard_name', $role->guard_name)
                ->get();

            $role->syncPermissions($permissions);
        }
    }

    private function isSuperadminRole(Role $role): bool
    {
        return in_array(strtolower(trim($role->name)), [
            'superadmin',
            'super admin',
        ], true);
    }

    private function parsePermissionName(string $permissionName): array
    {
        $actionLabels = [
            'list' => 'View',
            'view' => 'View',
            'store' => 'Create',
            'create' => 'Create',
            'update' => 'Update',
            'destroy' => 'Delete',
            'delete' => 'Delete',
            'setting' => 'Setting',
        ];

        $groupLabels = [
            'master-data' => 'Master Data',
            'account' => 'Account',
            'dashboard' => 'Dashboard',
            'data-pelamar' => 'Data Pelamar',
            'permintaan-kandidat' => 'Permintaan Kandidat',
        ];

        $parts = explode('.', $permissionName);

        $action = array_pop($parts);
        $moduleKey = array_pop($parts) ?? 'general';
        $groupKey = array_pop($parts) ?? 'general';

        $group = $groupLabels[$groupKey] ?? ucwords(str_replace('-', ' ', $groupKey));
        $module = ucwords(str_replace('-', ' ', $moduleKey));

        return [
            'group_key' => $groupKey,
            'module_key' => $moduleKey,
            'action' => $action,
            'group' => $group,
            'module' => $module,
            'action_label' => $actionLabels[$action] ?? ucwords(str_replace('-', ' ', $action)),
        ];
    }

    private function actionSortOrder(string $actionLabel): int
    {
        return match ($actionLabel) {
            'View' => 1,
            'Create' => 2,
            'Update' => 3,
            'Delete' => 4,
            'Setting' => 5,
            default => 99,
        };
    }
}
