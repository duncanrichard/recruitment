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
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data role berhasil diambil.',
            'data' => $roles,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_role' => ['required', 'string', 'max:100', 'unique:roles,nama_role'],
            'kode_role' => ['nullable', 'string', 'max:50', 'unique:roles,kode_role'],
            'keterangan' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $role = Role::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data role berhasil ditambahkan.',
            'data' => $role,
        ], 201);
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'nama_role' => [
                'required',
                'string',
                'max:100',
                Rule::unique('roles', 'nama_role')->ignore($role->id),
            ],
            'kode_role' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('roles', 'kode_role')->ignore($role->id),
            ],
            'keterangan' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $role->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data role berhasil diperbarui.',
            'data' => $role,
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