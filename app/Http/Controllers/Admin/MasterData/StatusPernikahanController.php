<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\StatusPernikahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StatusPernikahanController extends Controller
{
    public function list()
    {
        $data = StatusPernikahan::query()
            ->orderByDesc('created_at')
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
            'status_pernikahan' => ['required', 'string', 'max:255'],
        ]);

        $statusPernikahan = StatusPernikahan::create([
            'status_pernikahan' => $validated['status_pernikahan'],
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
        $validated = $request->validate([
            'status_pernikahan' => ['required', 'string', 'max:255'],
        ]);

        $statusPernikahan = StatusPernikahan::findOrFail($id);

        $statusPernikahan->update([
            'status_pernikahan' => $validated['status_pernikahan'],
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