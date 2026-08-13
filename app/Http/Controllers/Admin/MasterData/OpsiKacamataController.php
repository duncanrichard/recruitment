<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\OpsiKacamata;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OpsiKacamataController extends Controller
{
    public function list()
    {
        $data = OpsiKacamata::query()
            ->orderBy('opsi', 'asc')
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
            'opsi' => [
                'required',
                'string',
                'max:255',
                Rule::unique('opsi_kacamata', 'opsi')
                    ->whereNull('deleted_at'),
            ],
        ]);

        $opsiKacamata = OpsiKacamata::create([
            'opsi' => trim($validated['opsi']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data opsi kacamata berhasil ditambahkan.',
            'data' => $opsiKacamata,
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $opsiKacamata = OpsiKacamata::findOrFail($id);

        $validated = $request->validate([
            'opsi' => [
                'required',
                'string',
                'max:255',
                Rule::unique('opsi_kacamata', 'opsi')
                    ->ignore($opsiKacamata->id, 'id')
                    ->whereNull('deleted_at'),
            ],
        ]);

        $opsiKacamata->update([
            'opsi' => trim($validated['opsi']),
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
