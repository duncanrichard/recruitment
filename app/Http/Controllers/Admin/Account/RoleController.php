<?php

namespace App\Http\Controllers\Admin\Account;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    public function index()
    {
        return view('pages.admin.index');
    }

    public function list()
    {
        try {
            $roles = Role::query()
                ->withCount('permissions')
                ->orderBy('name', 'asc')
                ->get()
                ->map(function ($role) {
                    return $this->formatRole($role);
                });

            return response()->json([
                'success' => true,
                'message' => 'Data role berhasil diambil.',
                'data' => $roles,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data role.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $guardName = $request->input('guard_name', 'web');

        $validated = $request->validate([
            'nama_role' => [
                'required',
                'string',
                'max:100',
                Rule::unique('roles', 'name')
                    ->where('guard_name', $guardName),
            ],
            'guard_name' => [
                'nullable',
                'string',
                'max:100',
            ],
        ], [
            'nama_role.required' => 'Nama role wajib diisi.',
            'nama_role.unique' => 'Nama role sudah digunakan.',
            'guard_name.string' => 'Guard name harus berupa teks.',
        ]);

        try {
            $role = DB::transaction(function () use ($validated) {
                $role = Role::create([
                    'name' => trim($validated['nama_role']),
                    'guard_name' => $validated['guard_name'] ?? 'web',
                ]);

                app(PermissionRegistrar::class)->forgetCachedPermissions();

                return $role;
            });

            return response()->json([
                'success' => true,
                'message' => 'Data role berhasil ditambahkan.',
                'data' => $this->formatRole($role),
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data role gagal ditambahkan.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, Role $role)
    {
        if ($this->isSuperadminRole($role)) {
            return response()->json([
                'success' => false,
                'message' => 'Role Superadmin tidak boleh diubah.',
            ], 422);
        }

        $guardName = $request->input('guard_name', $role->guard_name ?? 'web');

        $validated = $request->validate([
            'nama_role' => [
                'required',
                'string',
                'max:100',
                Rule::unique('roles', 'name')
                    ->where('guard_name', $guardName)
                    ->ignore($role->id, 'id'),
            ],
            'guard_name' => [
                'nullable',
                'string',
                'max:100',
            ],
        ], [
            'nama_role.required' => 'Nama role wajib diisi.',
            'nama_role.unique' => 'Nama role sudah digunakan.',
            'guard_name.string' => 'Guard name harus berupa teks.',
        ]);

        try {
            DB::transaction(function () use ($validated, $role) {
                $role->update([
                    'name' => trim($validated['nama_role']),
                    'guard_name' => $validated['guard_name'] ?? $role->guard_name ?? 'web',
                ]);

                app(PermissionRegistrar::class)->forgetCachedPermissions();
            });

            return response()->json([
                'success' => true,
                'message' => 'Data role berhasil diperbarui.',
                'data' => $this->formatRole($role->fresh()->loadCount('permissions')),
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data role gagal diperbarui.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function destroy(Role $role)
    {
        if ($this->isSuperadminRole($role)) {
            return response()->json([
                'success' => false,
                'message' => 'Role Superadmin tidak boleh dihapus.',
            ], 422);
        }

        try {
            DB::transaction(function () use ($role) {
                $role->syncPermissions([]);
                $role->delete();

                app(PermissionRegistrar::class)->forgetCachedPermissions();
            });

            return response()->json([
                'success' => true,
                'message' => 'Data role berhasil dihapus.',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data role gagal dihapus.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    private function formatRole(Role $role): array
    {
        return [
            'id' => $role->id,
            'nama_role' => $role->name,
            'name' => $role->name,
            'guard_name' => $role->guard_name,
            'permissions_count' => $role->permissions_count ?? $role->permissions()->count(),
            'is_superadmin' => $this->isSuperadminRole($role),
            'created_at' => $role->created_at,
            'updated_at' => $role->updated_at,
        ];
    }

    private function isSuperadminRole(Role $role): bool
    {
        return in_array(strtolower(trim($role->name)), [
            'superadmin',
            'super admin',
        ], true);
    }
}