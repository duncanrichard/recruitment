<?php

namespace App\Http\Controllers\Admin\Account;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        return view('pages.admin.index');
    }

    public function list()
    {
        $roles = Role::query()
            ->latest()
            ->get()
            ->map(function ($role) {
                return [
                    'id' => $role->id,
                    'nama_role' => $role->name,
                    'name' => $role->name,
                    'guard_name' => $role->guard_name,
                    'created_at' => $role->created_at,
                    'updated_at' => $role->updated_at,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Data role berhasil diambil.',
            'data' => $roles,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_role' => [
                'required',
                'string',
                'max:100',
                Rule::unique('roles', 'name'),
            ],
            'guard_name' => [
                'nullable',
                'string',
                'max:100',
            ],
        ]);

        $role = Role::create([
            'name' => $validated['nama_role'],
            'guard_name' => $validated['guard_name'] ?? 'web',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data role berhasil ditambahkan.',
            'data' => [
                'id' => $role->id,
                'nama_role' => $role->name,
                'name' => $role->name,
                'guard_name' => $role->guard_name,
            ],
        ], 201);
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'nama_role' => [
                'required',
                'string',
                'max:100',
                Rule::unique('roles', 'name')->ignore($role->id),
            ],
            'guard_name' => [
                'nullable',
                'string',
                'max:100',
            ],
        ]);

        $role->update([
            'name' => $validated['nama_role'],
            'guard_name' => $validated['guard_name'] ?? $role->guard_name ?? 'web',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data role berhasil diperbarui.',
            'data' => [
                'id' => $role->id,
                'nama_role' => $role->name,
                'name' => $role->name,
                'guard_name' => $role->guard_name,
            ],
        ]);
    }

    public function destroy(Role $role)
    {
        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data role berhasil dihapus.',
        ]);
    }
}