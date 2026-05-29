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
                    'role:id,nama_role,kode_role,is_active',
                    'divisi:id,nama',
                ])
                ->orderByDesc('created_at')
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'email_verified_at' => $user->email_verified_at,
                        'role_id' => $user->role_id,
                        'role_label' => $user->role?->nama_role,
                        'role_kode' => $user->role?->kode_role,
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
                ->select('id', 'nama_role', 'kode_role', 'is_active')
                ->where('is_active', true)
                ->orderBy('nama_role')
                ->get();

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
            'name' => [
                'required',
                'string',
                'max:191',
            ],
            'email' => [
                'required',
                'email',
                'max:191',
                'unique:users,email',
            ],
            'password' => [
                'required',
                'string',
                'min:6',
                'confirmed',
            ],
            'role_id' => [
                'required',
                'integer',
                'exists:roles,id',
            ],
            'divisi_id' => [
                'nullable',
                'uuid',
                'exists:divisi,id',
            ],
            'email_verified_at' => [
                'nullable',
                'date',
            ],
        ]);

        try {
            DB::transaction(function () use ($validated) {
                User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => $validated['password'],
                    'role_id' => $validated['role_id'],
                    'divisi_id' => $validated['divisi_id'] ?? null,
                    'email_verified_at' => $validated['email_verified_at'] ?? null,
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'User berhasil ditambahkan.',
            ]);
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
            'name' => [
                'required',
                'string',
                'max:191',
            ],
            'email' => [
                'required',
                'email',
                'max:191',
                Rule::unique('users', 'email')->ignore($user->id, 'id'),
            ],
            'password' => [
                'nullable',
                'string',
                'min:6',
                'confirmed',
            ],
            'role_id' => [
                'required',
                'integer',
                'exists:roles,id',
            ],
            'divisi_id' => [
                'nullable',
                'uuid',
                'exists:divisi,id',
            ],
            'email_verified_at' => [
                'nullable',
                'date',
            ],
        ]);

        try {
            DB::transaction(function () use ($validated, $user) {
                $payload = [
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'role_id' => $validated['role_id'],
                    'divisi_id' => $validated['divisi_id'] ?? null,
                    'email_verified_at' => $validated['email_verified_at'] ?? null,
                ];

                if (!empty($validated['password'])) {
                    $payload['password'] = $validated['password'];
                }

                $user->update($payload);
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
            $user->delete();

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