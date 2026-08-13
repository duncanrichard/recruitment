<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Pendidikan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PendidikanController extends Controller
{
    public function list()
    {
        $data = Pendidikan::query()
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data pendidikan berhasil diambil.',
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pendidikan' => ['required', 'string', 'max:255'],
        ]);

        $pendidikan = Pendidikan::create([
            'pendidikan' => $validated['pendidikan'],
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data pendidikan berhasil ditambahkan.',
            'data' => $pendidikan,
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'pendidikan' => ['required', 'string', 'max:255'],
        ]);

        $pendidikan = Pendidikan::findOrFail($id);

        $pendidikan->update([
            'pendidikan' => $validated['pendidikan'],
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data pendidikan berhasil diperbarui.',
            'data' => $pendidikan,
        ]);
    }

    public function destroy(string $id)
    {
        $pendidikan = Pendidikan::findOrFail($id);

        $pendidikan->update([
            'deleted_by' => Auth::id(),
        ]);

        $pendidikan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data pendidikan berhasil dihapus.',
        ]);
    }
}
