<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Posisi;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PosisiController extends Controller
{
    public function list()
    {
        $data = Posisi::query()
            ->orderBy('nama_posisi')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_posisi' => [
                'required',
                'string',
                'max:255',
            ],
            'deskripsi' => [
                'nullable',
                'string',
            ],
            'str_aktif' => [
                'nullable',
                Rule::in(['active', 'non_active']),
            ],
        ]);

        $validated['str_aktif'] = $validated['str_aktif'] ?? 'active';

        $posisi = Posisi::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data posisi berhasil disimpan.',
            'data' => $posisi,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $posisi = Posisi::findOrFail($id);

        $validated = $request->validate([
            'nama_posisi' => [
                'required',
                'string',
                'max:255',
            ],
            'deskripsi' => [
                'nullable',
                'string',
            ],
            'str_aktif' => [
                'nullable',
                Rule::in(['active', 'non_active']),
            ],
        ]);

        $validated['str_aktif'] = $validated['str_aktif'] ?? 'active';

        $posisi->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data posisi berhasil diperbarui.',
            'data' => $posisi->fresh(),
        ]);
    }

    public function destroy($id)
    {
        $posisi = Posisi::findOrFail($id);

        try {
            $posisi->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data posisi berhasil dihapus.',
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data posisi tidak bisa dihapus karena masih digunakan oleh data lain.',
            ], 409);
        }
    }
}