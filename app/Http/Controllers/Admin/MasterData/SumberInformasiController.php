<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\SumberInformasi;
use Illuminate\Http\Request;

class SumberInformasiController extends Controller
{
    public function list()
    {
        $data = SumberInformasi::query()
            ->whereNull('deleted_by')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data sumber informasi berhasil diambil.',
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'informasi' => ['required', 'string', 'max:255'],
        ]);

        $sumberInformasi = SumberInformasi::create([
            'informasi' => $validated['informasi'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data sumber informasi berhasil ditambahkan.',
            'data' => $sumberInformasi,
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'informasi' => ['required', 'string', 'max:255'],
        ]);

        $sumberInformasi = SumberInformasi::findOrFail($id);

        $sumberInformasi->update([
            'informasi' => $validated['informasi'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data sumber informasi berhasil diperbarui.',
            'data' => $sumberInformasi,
        ]);
    }

    public function destroy(string $id)
    {
        $sumberInformasi = SumberInformasi::findOrFail($id);

        $sumberInformasi->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data sumber informasi berhasil dihapus.',
        ]);
    }
}