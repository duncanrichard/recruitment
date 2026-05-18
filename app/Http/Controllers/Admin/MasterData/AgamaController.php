<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Agama;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgamaController extends Controller
{
    public function list()
    {
        $data = Agama::query()
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data agama berhasil diambil.',
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'agama' => ['required', 'string', 'max:255'],
        ]);

        $agama = Agama::create([
            'agama' => $validated['agama'],
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data agama berhasil ditambahkan.',
            'data' => $agama,
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'agama' => ['required', 'string', 'max:255'],
        ]);

        $agama = Agama::findOrFail($id);

        $agama->update([
            'agama' => $validated['agama'],
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data agama berhasil diperbarui.',
            'data' => $agama,
        ]);
    }

    public function destroy(string $id)
    {
        $agama = Agama::findOrFail($id);

        $agama->update([
            'deleted_by' => Auth::id(),
        ]);

        $agama->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data agama berhasil dihapus.',
        ]);
    }
}