<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\SumberInformasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class SumberInformasiController extends Controller
{
    public function list()
    {
        $query = SumberInformasi::query()
            ->orderBy('informasi', 'asc');

        if (Schema::hasColumn('sumber_informasi', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        $data = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'Data sumber informasi berhasil diambil.',
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $uniqueRule = Rule::unique('sumber_informasi', 'informasi');

        if (Schema::hasColumn('sumber_informasi', 'deleted_at')) {
            $uniqueRule->whereNull('deleted_at');
        }

        $validated = $request->validate([
            'informasi' => [
                'required',
                'string',
                'max:255',
                $uniqueRule,
            ],
        ], [
            'informasi.required' => 'Sumber informasi wajib diisi.',
            'informasi.unique' => 'Sumber informasi sudah tersedia.',
        ]);

        $payload = [
            'informasi' => trim($validated['informasi']),
        ];

        if (Schema::hasColumn('sumber_informasi', 'created_by')) {
            $payload['created_by'] = Auth::id();
        }

        $sumberInformasi = SumberInformasi::create($payload);

        return response()->json([
            'success' => true,
            'message' => 'Data sumber informasi berhasil ditambahkan.',
            'data' => $sumberInformasi,
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $sumberInformasi = SumberInformasi::query()->findOrFail($id);

        $uniqueRule = Rule::unique('sumber_informasi', 'informasi')
            ->ignore($sumberInformasi->id, 'id');

        if (Schema::hasColumn('sumber_informasi', 'deleted_at')) {
            $uniqueRule->whereNull('deleted_at');
        }

        $validated = $request->validate([
            'informasi' => [
                'required',
                'string',
                'max:255',
                $uniqueRule,
            ],
        ], [
            'informasi.required' => 'Sumber informasi wajib diisi.',
            'informasi.unique' => 'Sumber informasi sudah tersedia.',
        ]);

        $payload = [
            'informasi' => trim($validated['informasi']),
        ];

        if (Schema::hasColumn('sumber_informasi', 'updated_by')) {
            $payload['updated_by'] = Auth::id();
        }

        $sumberInformasi->update($payload);

        return response()->json([
            'success' => true,
            'message' => 'Data sumber informasi berhasil diperbarui.',
            'data' => $sumberInformasi->fresh(),
        ]);
    }

    public function destroy(string $id)
    {
        $sumberInformasi = SumberInformasi::query()->findOrFail($id);

        if (Schema::hasColumn('sumber_informasi', 'deleted_by')) {
            $sumberInformasi->update([
                'deleted_by' => Auth::id(),
            ]);
        }

        $sumberInformasi->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data sumber informasi berhasil dihapus.',
        ]);
    }
}
