<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DivisiController extends Controller
{
    public function list()
    {
        $data = Divisi::query()
            ->orderBy('nama', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data divisi berhasil diambil.',
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
                Rule::unique('divisi', 'nama')->whereNull('deleted_at'),
            ],
        ]);

        $divisi = Divisi::create([
            'nama' => $validated['nama'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data divisi berhasil disimpan.',
            'data' => $divisi,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $divisi = Divisi::findOrFail($id);

        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:255',
                Rule::unique('divisi', 'nama')
                    ->ignore($divisi->id, 'id')
                    ->whereNull('deleted_at'),
            ],
        ]);

        $divisi->update([
            'nama' => $validated['nama'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data divisi berhasil diperbarui.',
            'data' => $divisi,
        ]);
    }

    public function destroy(string $id)
    {
        $divisi = Divisi::findOrFail($id);

        $divisi->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data divisi berhasil dihapus.',
        ]);
    }
}