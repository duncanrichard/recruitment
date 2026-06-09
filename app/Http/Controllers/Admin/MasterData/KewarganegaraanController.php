<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Kewarganegaraan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class KewarganegaraanController extends Controller
{
    public function list()
    {
        $data = Kewarganegaraan::query()
            ->orderBy('kewarganegaraan', 'asc')
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
            'kewarganegaraan' => [
                'required',
                'string',
                'max:255',
                Rule::unique('kewarganegaraan', 'kewarganegaraan')->whereNull('deleted_at'),
            ],
        ]);

        $kewarganegaraan = Kewarganegaraan::create([
            'kewarganegaraan' => trim($validated['kewarganegaraan']),
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
        $kewarganegaraan = Kewarganegaraan::findOrFail($id);

        $validated = $request->validate([
            'kewarganegaraan' => [
                'required',
                'string',
                'max:255',
                Rule::unique('kewarganegaraan', 'kewarganegaraan')
                    ->ignore($kewarganegaraan->id, 'id')
                    ->whereNull('deleted_at'),
            ],
        ]);

        $kewarganegaraan->update([
            'kewarganegaraan' => trim($validated['kewarganegaraan']),
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