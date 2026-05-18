<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\OpsiKacamata;
use Illuminate\Http\Request;

class OpsiKacamataController extends Controller
{
    public function list()
    {
        $data = OpsiKacamata::query()
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data opsi kacamata berhasil diambil.',
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'opsi' => ['required', 'string', 'max:255'],
        ]);

        $opsiKacamata = OpsiKacamata::create([
            'opsi' => $validated['opsi'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data opsi kacamata berhasil ditambahkan.',
            'data' => $opsiKacamata,
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'opsi' => ['required', 'string', 'max:255'],
        ]);

        $opsiKacamata = OpsiKacamata::findOrFail($id);

        $opsiKacamata->update([
            'opsi' => $validated['opsi'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data opsi kacamata berhasil diperbarui.',
            'data' => $opsiKacamata,
        ]);
    }

    public function destroy(string $id)
    {
        $opsiKacamata = OpsiKacamata::findOrFail($id);

        $opsiKacamata->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data opsi kacamata berhasil dihapus.',
        ]);
    }
}