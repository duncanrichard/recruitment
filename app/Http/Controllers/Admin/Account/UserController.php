<?php

namespace App\Http\Controllers\Admin\Account;

use App\Http\Controllers\Controller;
use App\Models\DataPerusahaan;
use App\Models\Divisi;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\PermissionRegistrar;

class UserController extends Controller
{
    public function index()
    {
        return view('pages.admin.index');
    }

    public function list()
    {
        try {
            $users = User::query()
                ->with([
                    'roles:id,name,guard_name',
                    'divisi:id,nama',
                    'perusahaans:id,kode,nama_perusahaan',
                ])
                ->orderBy('name', 'asc')
                ->get()
                ->map(function ($user) {
                    return $this->formatUser($user);
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
                ->orderBy('name', 'asc')
                ->get()
                ->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'guard_name' => $role->guard_name,
                        'nama_role' => $role->name,
                        'kode_role' => $role->guard_name,
                    ];
                });

            $divisi = Divisi::query()
                ->select('id', 'nama')
                ->orderBy('nama', 'asc')
                ->get();

            $perusahaan = DataPerusahaan::query()
                ->select('id', 'kode', 'nama_perusahaan')
                ->orderBy('nama_perusahaan', 'asc')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'kode' => $item->kode,
                        'nama_perusahaan' => $item->nama_perusahaan,
                        'label' => trim(($item->kode ? $item->kode . ' - ' : '') . $item->nama_perusahaan),
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Data option berhasil diambil.',
                'data' => [
                    'roles' => $roles,
                    'divisi' => $divisi,
                    'perusahaan' => $perusahaan,
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
                Rule::unique('users', 'email'),
            ],
            'password' => [
                'required',
                'string',
                'min:6',
                'confirmed',
            ],
            'role_id' => [
                'required',
                'uuid',
                'exists:roles,id',
            ],
            'divisi_id' => [
                'nullable',
                'uuid',
                'exists:divisi,id',
            ],
            'perusahaan_ids' => [
                'nullable',
                'array',
            ],
            'perusahaan_ids.*' => [
                'uuid',
                'exists:data_perusahaan,id',
            ],
            'email_verified_at' => [
                'nullable',
                'date',
            ],
        ], [
            'name.required' => 'Nama user wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
            'role_id.required' => 'Role wajib dipilih.',
            'role_id.exists' => 'Role tidak valid.',
            'divisi_id.exists' => 'Divisi tidak valid.',
            'perusahaan_ids.array' => 'Format perusahaan tidak valid.',
            'perusahaan_ids.*.exists' => 'Perusahaan tidak valid.',
        ]);

        try {
            $user = DB::transaction(function () use ($validated) {
                $role = Role::query()
                    ->where('id', $validated['role_id'])
                    ->firstOrFail();

                $perusahaanIds = $this->normalizePerusahaanIds($validated['perusahaan_ids'] ?? []);

                $createPayload = [
                    'uuid' => (string) Str::uuid(),
                    'name' => trim($validated['name']),
                    'email' => strtolower(trim($validated['email'])),
                    'password' => $validated['password'],
                    'divisi_id' => $validated['divisi_id'] ?? null,
                    'email_verified_at' => $validated['email_verified_at'] ?? null,
                ];

                if (Schema::hasColumn('users', 'perusahaan_id')) {
                    $createPayload['perusahaan_id'] = $perusahaanIds[0] ?? null;
                }

                $user = User::query()->create($createPayload);

                $user->assignRole($role);

                $this->syncUserPerusahaans($user, $perusahaanIds);

                app(PermissionRegistrar::class)->forgetCachedPermissions();

                return $user->fresh()->load([
                    'roles:id,name,guard_name',
                    'divisi:id,nama',
                    'perusahaans:id,kode,nama_perusahaan',
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'User berhasil ditambahkan.',
                'data' => $this->formatUser($user),
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
            'name' => [
                'required',
                'string',
                'max:191',
            ],
            'email' => [
                'required',
                'email',
                'max:191',
                Rule::unique('users', 'email')->ignore($user->uuid, 'uuid'),
            ],
            'password' => [
                'nullable',
                'string',
                'min:6',
                'confirmed',
            ],
            'role_id' => [
                'required',
                'uuid',
                'exists:roles,id',
            ],
            'divisi_id' => [
                'nullable',
                'uuid',
                'exists:divisi,id',
            ],
            'perusahaan_ids' => [
                'nullable',
                'array',
            ],
            'perusahaan_ids.*' => [
                'uuid',
                'exists:data_perusahaan,id',
            ],
            'email_verified_at' => [
                'nullable',
                'date',
            ],
        ], [
            'name.required' => 'Nama user wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
            'role_id.required' => 'Role wajib dipilih.',
            'role_id.exists' => 'Role tidak valid.',
            'divisi_id.exists' => 'Divisi tidak valid.',
            'perusahaan_ids.array' => 'Format perusahaan tidak valid.',
            'perusahaan_ids.*.exists' => 'Perusahaan tidak valid.',
        ]);

        try {
            $updatedUser = DB::transaction(function () use ($validated, $user) {
                $role = Role::query()
                    ->where('id', $validated['role_id'])
                    ->firstOrFail();

                $perusahaanIds = $this->normalizePerusahaanIds($validated['perusahaan_ids'] ?? []);

                $payload = [
                    'name' => trim($validated['name']),
                    'email' => strtolower(trim($validated['email'])),
                    'divisi_id' => $validated['divisi_id'] ?? null,
                    'email_verified_at' => $validated['email_verified_at'] ?? null,
                ];

                if (Schema::hasColumn('users', 'perusahaan_id')) {
                    $payload['perusahaan_id'] = $perusahaanIds[0] ?? null;
                }

                if (! empty($validated['password'])) {
                    $payload['password'] = $validated['password'];
                }

                $user->update($payload);

                $user->syncRoles([$role]);

                $this->syncUserPerusahaans($user, $perusahaanIds);

                app(PermissionRegistrar::class)->forgetCachedPermissions();

                return $user->fresh()->load([
                    'roles:id,name,guard_name',
                    'divisi:id,nama',
                    'perusahaans:id,kode,nama_perusahaan',
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'User berhasil diperbarui.',
                'data' => $this->formatUser($updatedUser),
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
        $user->loadMissing('roles:id,name,guard_name');

        if ($this->userHasSuperadminRole($user)) {
            return response()->json([
                'success' => false,
                'message' => 'User dengan role Superadmin tidak boleh dihapus.',
            ], 422);
        }

        try {
            DB::transaction(function () use ($user) {
                $this->syncUserPerusahaans($user, []);

                $user->syncRoles([]);

                $user->delete();

                app(PermissionRegistrar::class)->forgetCachedPermissions();
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

    private function syncUserPerusahaans(User $user, array $perusahaanIds): void
    {
        $perusahaanIds = $this->normalizePerusahaanIds($perusahaanIds);

        if (! Schema::hasTable('data_perusahaan_user')) {
            return;
        }

        DB::table('data_perusahaan_user')
            ->where('user_id', $user->uuid)
            ->delete();

        if (empty($perusahaanIds)) {
            return;
        }

        $now = now();

        $rows = collect($perusahaanIds)
            ->map(function ($perusahaanId) use ($user, $now) {
                $row = [
                    'user_id' => $user->uuid,
                    'perusahaan_id' => $perusahaanId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if (Schema::hasColumn('data_perusahaan_user', 'id')) {
                    $row['id'] = (string) Str::uuid();
                }

                return $row;
            })
            ->values()
            ->all();

        DB::table('data_perusahaan_user')->insert($rows);
    }

    private function normalizePerusahaanIds($perusahaanIds): array
    {
        return collect($perusahaanIds ?: [])
            ->filter()
            ->map(function ($id) {
                return (string) $id;
            })
            ->unique()
            ->values()
            ->all();
    }

    private function formatUser(User $user): array
    {
        $role = $user->roles->first();

        $perusahaans = $user->perusahaans ?? collect();

        return [
            // ini sengaja tetap dikirim sebagai id
            // supaya React yang masih pakai item.id tidak perlu diubah
            'id' => $user->uuid,
            'uuid' => $user->uuid,

            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,

            'role_id' => $role?->id,
            'role_label' => $role?->name,
            'role_guard' => $role?->guard_name,
            'role_kode' => $role?->guard_name,
            'is_superadmin' => $role ? $this->isSuperadminRole($role) : false,

            'divisi_id' => $user->divisi_id,
            'divisi_label' => $user->divisi?->nama,

            'perusahaan_id' => $user->perusahaan_id ?? null,
            'perusahaan_ids' => $perusahaans->pluck('id')->values(),
            'perusahaan_kode' => $perusahaans->pluck('kode')->filter()->implode(', '),
            'perusahaan_label' => $perusahaans->pluck('nama_perusahaan')->filter()->implode(', '),
            'perusahaans' => $perusahaans->map(function ($item) {
                return [
                    'id' => $item->id,
                    'kode' => $item->kode,
                    'nama_perusahaan' => $item->nama_perusahaan,
                    'label' => trim(($item->kode ? $item->kode . ' - ' : '') . $item->nama_perusahaan),
                ];
            })->values(),

            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];
    }

    private function userHasSuperadminRole(User $user): bool
    {
        $user->loadMissing('roles:id,name,guard_name');

        return $user->roles->contains(function ($role) {
            return $this->isSuperadminRole($role);
        });
    }

    private function isSuperadminRole(Role $role): bool
    {
        return in_array(strtolower(trim($role->name)), [
            'superadmin',
            'super admin',
        ], true);
    }
}