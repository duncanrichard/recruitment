<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Agama;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AgamaController extends Controller
{
    public function list()
    {
        $data = Agama::query()
            ->orderBy('agama', 'asc')
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
            'agama' => [
                'required',
                'string',
                'max:255',
                Rule::unique('agama', 'agama')->whereNull('deleted_at'),
            ],
        ]);

        $agama = Agama::create([
            'agama' => trim($validated['agama']),
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
        $agama = Agama::findOrFail($id);

        $validated = $request->validate([
            'agama' => [
                'required',
                'string',
                'max:255',
                Rule::unique('agama', 'agama')
                    ->ignore($agama->id, 'id')
                    ->whereNull('deleted_at'),
            ],
        ]);

        $agama->update([
            'agama' => trim($validated['agama']),
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
