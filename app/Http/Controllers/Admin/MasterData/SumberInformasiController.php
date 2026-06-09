<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\SumberInformasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SumberInformasiController extends Controller
{
    public function list()
    {
        $data = SumberInformasi::query()
            ->orderBy('informasi', 'asc')
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
            'informasi' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sumber_informasi', 'informasi')->whereNull('deleted_at'),
            ],
        ]);

        $payload = [
            'informasi' => trim($validated['informasi']),
        ];

        $sumberInformasi = new SumberInformasi();

        if (array_key_exists('created_by', $sumberInformasi->getAttributes())) {
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
        $sumberInformasi = SumberInformasi::findOrFail($id);

        $validated = $request->validate([
            'informasi' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sumber_informasi', 'informasi')
                    ->ignore($sumberInformasi->id, 'id')
                    ->whereNull('deleted_at'),
            ],
        ]);

        $payload = [
            'informasi' => trim($validated['informasi']),
        ];

        if (array_key_exists('updated_by', $sumberInformasi->getAttributes())) {
            $payload['updated_by'] = Auth::id();
        }

        $sumberInformasi->update($payload);

        return response()->json([
            'success' => true,
            'message' => 'Data sumber informasi berhasil diperbarui.',
            'data' => $sumberInformasi,
        ]);
    }

    public function destroy(string $id)
    {
        $sumberInformasi = SumberInformasi::findOrFail($id);

        if (array_key_exists('deleted_by', $sumberInformasi->getAttributes())) {
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