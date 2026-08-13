<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\StatusPernikahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StatusPernikahanController extends Controller
{
    public function list()
    {
        $data = StatusPernikahan::query()
            ->orderBy('status_pernikahan', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data status pernikahan berhasil diambil.',
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'status_pernikahan' => [
                'required',
                'string',
                'max:255',
                Rule::unique('status_pernikahan', 'status_pernikahan')
                    ->whereNull('deleted_at'),
            ],
        ]);

        $statusPernikahan = StatusPernikahan::create([
            'status_pernikahan' => trim($validated['status_pernikahan']),
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data status pernikahan berhasil ditambahkan.',
            'data' => $statusPernikahan,
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $statusPernikahan = StatusPernikahan::findOrFail($id);

        $validated = $request->validate([
            'status_pernikahan' => [
                'required',
                'string',
                'max:255',
                Rule::unique('status_pernikahan', 'status_pernikahan')
                    ->ignore($statusPernikahan->id, 'id')
                    ->whereNull('deleted_at'),
            ],
        ]);

        $statusPernikahan->update([
            'status_pernikahan' => trim($validated['status_pernikahan']),
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data status pernikahan berhasil diperbarui.',
            'data' => $statusPernikahan,
        ]);
    }

    public function destroy(string $id)
    {
        $statusPernikahan = StatusPernikahan::findOrFail($id);

        $statusPernikahan->update([
            'deleted_by' => Auth::id(),
        ]);

        $statusPernikahan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data status pernikahan berhasil dihapus.',
        ]);
    }
}
