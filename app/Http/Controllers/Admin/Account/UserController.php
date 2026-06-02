<?php

namespace App\Http\Controllers\Admin\Account;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        return view('admin');
    }

    public function list()
    {
        try {
            $users = User::query()
                ->with([
                    'roles:id,name,guard_name',
                    'divisi:id,nama',
                ])
                ->orderByDesc('created_at')
                ->get()
                ->map(function ($user) {
                    $role = $user->roles->first();

                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'email_verified_at' => $user->email_verified_at,

                        'role_id' => $role?->id,
                        'role_label' => $role?->name,
                        'role_guard' => $role?->guard_name,
                        'role_kode' => $role?->guard_name,

                        'divisi_id' => $user->divisi_id,
                        'divisi_label' => $user->divisi?->nama,

                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Data user berhasil diambil.',
                'data' => $users,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data user.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function options()
    {
        try {
            $roles = Role::query()
                ->select('id', 'name', 'guard_name')
                ->orderBy('name')
                ->get()
                ->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'guard_name' => $role->guard_name,

                        // fallback supaya frontend lama tetap aman
                        'nama_role' => $role->name,
                        'kode_role' => $role->guard_name,
                    ];
                });

            $divisi = Divisi::query()
                ->select('id', 'nama')
                ->orderBy('nama')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Data option berhasil diambil.',
                'data' => [
                    'roles' => $roles,
                    'divisi' => $divisi,
                ],
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data option.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', 'max:191', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'role_id' => ['required', 'uuid', 'exists:roles,id'],
            'divisi_id' => ['nullable', 'uuid', 'exists:divisi,id'],
            'email_verified_at' => ['nullable', 'date'],
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $role = Role::query()
                    ->where('id', $validated['role_id'])
                    ->firstOrFail();

                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => $validated['password'],
                    'divisi_id' => $validated['divisi_id'] ?? null,
                    'email_verified_at' => $validated['email_verified_at'] ?? null,
                ]);

                $user->assignRole($role);
            });

            return response()->json([
                'success' => true,
                'message' => 'User berhasil ditambahkan.',
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'User gagal ditambahkan.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'email' => [
                'required',
                'email',
                'max:191',
                Rule::unique('users', 'email')->ignore($user->id, 'id'),
            ],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'role_id' => ['required', 'uuid', 'exists:roles,id'],
            'divisi_id' => ['nullable', 'uuid', 'exists:divisi,id'],
            'email_verified_at' => ['nullable', 'date'],
        ]);

        try {
            DB::transaction(function () use ($validated, $user) {
                $role = Role::query()
                    ->where('id', $validated['role_id'])
                    ->firstOrFail();

                $payload = [
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'divisi_id' => $validated['divisi_id'] ?? null,
                    'email_verified_at' => $validated['email_verified_at'] ?? null,
                ];

                if (! empty($validated['password'])) {
                    $payload['password'] = $validated['password'];
                }

                $user->update($payload);

                $user->syncRoles([$role]);
            });

            return response()->json([
                'success' => true,
                'message' => 'User berhasil diperbarui.',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'User gagal diperbarui.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function destroy(User $user)
    {
        try {
            DB::transaction(function () use ($user) {
                $user->syncRoles([]);
                $user->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'User berhasil dihapus.',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'User gagal dihapus.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}