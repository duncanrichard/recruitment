<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Kewarganegaraan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KewarganegaraanController extends Controller
{
    public function list()
    {
        $data = Kewarganegaraan::query()
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data kewarganegaraan berhasil diambil.',
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kewarganegaraan' => ['required', 'string', 'max:255'],
        ]);

        $kewarganegaraan = Kewarganegaraan::create([
            'kewarganegaraan' => $validated['kewarganegaraan'],
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data kewarganegaraan berhasil ditambahkan.',
            'data' => $kewarganegaraan,
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'kewarganegaraan' => ['required', 'string', 'max:255'],
        ]);

        $kewarganegaraan = Kewarganegaraan::findOrFail($id);

        $kewarganegaraan->update([
            'kewarganegaraan' => $validated['kewarganegaraan'],
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data kewarganegaraan berhasil diperbarui.',
            'data' => $kewarganegaraan,
        ]);
    }

    public function destroy(string $id)
    {
        $kewarganegaraan = Kewarganegaraan::findOrFail($id);

        $kewarganegaraan->update([
            'deleted_by' => Auth::id(),
        ]);

        $kewarganegaraan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data kewarganegaraan berhasil dihapus.',
        ]);
    }
}