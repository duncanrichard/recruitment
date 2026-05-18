<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\DataPerusahaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DataPerusahaanController extends Controller
{
    public function list()
    {
        $data = DataPerusahaan::query()
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data perusahaan berhasil diambil.',
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:50'],
            'nama_perusahaan' => ['required', 'string', 'max:255'],
        ]);

        $perusahaan = DataPerusahaan::create([
            'kode' => $validated['kode'],
            'nama_perusahaan' => $validated['nama_perusahaan'],
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data perusahaan berhasil ditambahkan.',
            'data' => $perusahaan,
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:50'],
            'nama_perusahaan' => ['required', 'string', 'max:255'],
        ]);

        $perusahaan = DataPerusahaan::findOrFail($id);

        $perusahaan->update([
            'kode' => $validated['kode'],
            'nama_perusahaan' => $validated['nama_perusahaan'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data perusahaan berhasil diperbarui.',
            'data' => $perusahaan,
        ]);
    }

    public function destroy(string $id)
    {
        $perusahaan = DataPerusahaan::findOrFail($id);

        $perusahaan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data perusahaan berhasil dihapus.',
        ]);
    }
}