<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Posisi;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class PosisiController extends Controller
{
    public function list()
    {
        try {
            if (! Schema::hasTable((new Posisi())->getTable())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tabel posisi tidak ditemukan.',
                    'error' => 'Table not found: ' . (new Posisi())->getTable(),
                ], 500);
            }

            $query = Posisi::query();

            if (Schema::hasColumn((new Posisi())->getTable(), 'nama_posisi')) {
                $query->orderBy('nama_posisi');
            } else {
                $query->orderByDesc('created_at');
            }

            $data = $query->get();

            return response()->json([
                'success' => true,
                'message' => 'Data posisi berhasil diambil.',
                'data' => $data,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data posisi.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_posisi' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'str_aktif' => ['nullable', Rule::in(['active', 'non_active'])],
        ]);

        try {
            $validated['str_aktif'] = $validated['str_aktif'] ?? 'active';

            $posisi = Posisi::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Data posisi berhasil disimpan.',
                'data' => $posisi,
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data posisi gagal disimpan.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_posisi' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'str_aktif' => ['nullable', Rule::in(['active', 'non_active'])],
        ]);

        try {
            $posisi = Posisi::findOrFail($id);

            $validated['str_aktif'] = $validated['str_aktif'] ?? 'active';

            $posisi->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Data posisi berhasil diperbarui.',
                'data' => $posisi->fresh(),
            ]);
        } catch (ModelNotFoundException $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data posisi tidak ditemukan.',
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data posisi gagal diperbarui.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $posisi = Posisi::findOrFail($id);

            $posisi->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data posisi berhasil dihapus.',
            ]);
        } catch (ModelNotFoundException $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data posisi tidak ditemukan.',
            ], 404);
        } catch (QueryException $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data posisi tidak bisa dihapus karena masih digunakan oleh data lain.',
                'error' => $th->getMessage(),
            ], 409);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data posisi gagal dihapus.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}