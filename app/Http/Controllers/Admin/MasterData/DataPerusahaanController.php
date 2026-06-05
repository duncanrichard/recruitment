<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\DataPerusahaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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
            'nama_perusahaan' => ['required', 'string', 'max:255'],
            'no_wa' => ['required', 'string', 'max:30'],
            'token_api_wa' => ['nullable', 'string'],
        ], [
            'nama_perusahaan.required' => 'Nama perusahaan wajib diisi.',
            'no_wa.required' => 'Nomer perusahaan wajib diisi.',
            'no_wa.max' => 'Nomer perusahaan maksimal 30 karakter.',
            'token_api_wa.string' => 'Token API WA harus berupa teks.',
        ]);

        $perusahaan = DataPerusahaan::create([
            'kode' => $this->generateKodePerusahaan(),
            'nama_perusahaan' => $validated['nama_perusahaan'],
            'no_wa' => $this->normalizePhone($validated['no_wa']),
            'token_api_wa' => $this->normalizeToken($validated['token_api_wa'] ?? null),
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
            'nama_perusahaan' => ['required', 'string', 'max:255'],
            'no_wa' => ['required', 'string', 'max:30'],
            'token_api_wa' => ['nullable', 'string'],
        ], [
            'nama_perusahaan.required' => 'Nama perusahaan wajib diisi.',
            'no_wa.required' => 'Nomer perusahaan wajib diisi.',
            'no_wa.max' => 'Nomer perusahaan maksimal 30 karakter.',
            'token_api_wa.string' => 'Token API WA harus berupa teks.',
        ]);

        $perusahaan = DataPerusahaan::findOrFail($id);

        $perusahaan->update([
            'nama_perusahaan' => $validated['nama_perusahaan'],
            'no_wa' => $this->normalizePhone($validated['no_wa']),
            'token_api_wa' => $this->normalizeToken($validated['token_api_wa'] ?? null),
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

    private function generateKodePerusahaan(): string
    {
        do {
            $kode = 'PRS-' . now()->format('ymd') . '-' . strtoupper(Str::random(5));
        } while (DataPerusahaan::where('kode', $kode)->exists());

        return $kode;
    }

    private function normalizePhone(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);
        $value = preg_replace('/[^0-9+]/', '', $value);

        return $value !== '' ? $value : null;
    }

    private function normalizeToken(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }
}