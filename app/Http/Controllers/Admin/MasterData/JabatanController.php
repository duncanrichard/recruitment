<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Jabatan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JabatanController extends Controller
{
    public function list()
    {
        $data = Jabatan::query()
            ->orderBy('nama', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data jabatan berhasil diambil.',
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:255',
                Rule::unique('jabatan', 'nama')->whereNull('deleted_at'),
            ],
        ]);

        $jabatan = Jabatan::create([
            'nama' => $validated['nama'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data jabatan berhasil disimpan.',
            'data' => $jabatan,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $jabatan = Jabatan::findOrFail($id);

        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:255',
                Rule::unique('jabatan', 'nama')
                    ->ignore($jabatan->id, 'id')
                    ->whereNull('deleted_at'),
            ],
        ]);

        $jabatan->update([
            'nama' => $validated['nama'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data jabatan berhasil diperbarui.',
            'data' => $jabatan,
        ]);
    }

    public function destroy(string $id)
    {
        $jabatan = Jabatan::findOrFail($id);

        $jabatan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data jabatan berhasil dihapus.',
        ]);
    }
}
