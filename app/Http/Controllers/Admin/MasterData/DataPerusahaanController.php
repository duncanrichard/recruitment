<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\DataPerusahaan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DataPerusahaanController extends Controller
{
    public function list(): JsonResponse
    {
        $data = DataPerusahaan::query()
            ->orderBy('nama_perusahaan', 'asc')
            ->get()
            ->map(function ($item) {
                $nomorPerusahaan = $this->normalizeWhatsappNumber($item->no_wa ?? null);
                $tokenApiWa = $this->normalizeToken($item->token_api_wa ?? null);

                $statusWa = $this->checkFonnteDevice($nomorPerusahaan, $tokenApiWa);

                $item->wa_status = $statusWa['status'] ?? 'unknown';
                $item->wa_status_label = $statusWa['label'] ?? 'Belum Dicek';
                $item->wa_status_message = $statusWa['message'] ?? '-';
                $item->wa_device_number = $statusWa['device_number'] ?? null;
                $item->wa_device_status = $statusWa['device_status'] ?? null;

                return $item;
            });

        return response()->json([
            'success' => true,
            'message' => 'Data perusahaan berhasil diambil.',
            'data' => $data,
        ]);
    }

    public function store(Request $request): JsonResponse
{
    $validated = $request->validate([
        'nama_perusahaan' => [
            'required',
            'string',
            'max:255',
            Rule::unique('data_perusahaan', 'nama_perusahaan')->whereNull('deleted_at'),
        ],
        'no_wa' => ['required', 'string', 'max:30'],
        'token_api_wa' => ['nullable', 'string'],
    ], [
        'nama_perusahaan.required' => 'Nama perusahaan wajib diisi.',
        'nama_perusahaan.unique' => 'Nama perusahaan sudah digunakan.',
        'no_wa.required' => 'Nomor perusahaan wajib diisi.',
        'no_wa.max' => 'Nomor perusahaan maksimal 30 karakter.',
        'token_api_wa.string' => 'Token API WA harus berupa teks.',
    ]);

    $nomorPerusahaan = $this->normalizeWhatsappNumber($validated['no_wa']);
    $tokenApiWa = $this->normalizeToken($validated['token_api_wa'] ?? null);

    if ($tokenApiWa) {
        $this->validateFonnteDeviceOrFail($nomorPerusahaan, $tokenApiWa);
    }

    $perusahaan = DataPerusahaan::create([
        'kode' => $this->generateKodePerusahaan(),
        'nama_perusahaan' => trim($validated['nama_perusahaan']),
        'no_wa' => $nomorPerusahaan,
        'token_api_wa' => $tokenApiWa,
        'created_by' => Auth::id(),
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Data perusahaan berhasil ditambahkan.',
        'data' => $perusahaan,
    ], 201);
}

    public function update(Request $request, string $id): JsonResponse
    {
        $perusahaan = DataPerusahaan::findOrFail($id);

        $validated = $request->validate([
            'nama_perusahaan' => [
                'required',
                'string',
                'max:255',
                Rule::unique('data_perusahaan', 'nama_perusahaan')
                    ->ignore($perusahaan->id, 'id')
                    ->whereNull('deleted_at'),
            ],
            'no_wa' => ['required', 'string', 'max:30'],
            'token_api_wa' => ['required', 'string'],
        ], [
            'nama_perusahaan.required' => 'Nama perusahaan wajib diisi.',
            'nama_perusahaan.unique' => 'Nama perusahaan sudah digunakan.',
            'no_wa.required' => 'Nomor perusahaan wajib diisi.',
            'no_wa.max' => 'Nomor perusahaan maksimal 30 karakter.',
            'token_api_wa.required' => 'Token API WA wajib diisi.',
            'token_api_wa.string' => 'Token API WA harus berupa teks.',
        ]);

        $nomorPerusahaan = $this->normalizeWhatsappNumber($validated['no_wa']);
        $tokenApiWa = $this->normalizeToken($validated['token_api_wa']);

        /*
         * Validasi token tetap jalan.
         * Tetapi kalau nomor sesuai token dan device belum connect,
         * data tetap boleh di-update.
         */
        $this->validateFonnteDeviceOrFail($nomorPerusahaan, $tokenApiWa);

        $payload = [
            'nama_perusahaan' => trim($validated['nama_perusahaan']),
            'no_wa' => $nomorPerusahaan,
            'token_api_wa' => $tokenApiWa,
        ];

        if (array_key_exists('updated_by', $perusahaan->getAttributes())) {
            $payload['updated_by'] = Auth::id();
        }

        $perusahaan->update($payload);

        return response()->json([
            'success' => true,
            'message' => 'Data perusahaan berhasil diperbarui.',
            'data' => $perusahaan->fresh(),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $perusahaan = DataPerusahaan::findOrFail($id);

        if (array_key_exists('deleted_by', $perusahaan->getAttributes())) {
            $perusahaan->update([
                'deleted_by' => Auth::id(),
            ]);
        }

        $perusahaan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data perusahaan berhasil dihapus.',
        ]);
    }

    public function validasiWa(string $id): JsonResponse
    {
        $perusahaan = DataPerusahaan::findOrFail($id);

        $nomorPerusahaan = $this->normalizeWhatsappNumber($perusahaan->no_wa ?? null);
        $tokenApiWa = $this->normalizeToken($perusahaan->token_api_wa ?? null);

        $result = $this->checkFonnteDevice($nomorPerusahaan, $tokenApiWa);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'data' => [
                'perusahaan_id' => $perusahaan->id,
                'nama_perusahaan' => $perusahaan->nama_perusahaan,
                'nomor_database' => $nomorPerusahaan,
                'nomor_device' => $result['device_number'] ?? null,
                'device_status' => $result['device_status'] ?? null,
                'wa_status' => $result['status'] ?? 'unknown',
                'wa_status_label' => $result['label'] ?? 'Belum Dicek',
                'wa_status_message' => $result['message'] ?? '-',
                'fonnte_response' => $result['fonnte_response'] ?? null,
            ],
        ], $result['success'] ? 200 : 422);
    }

    private function generateKodePerusahaan(): string
    {
        do {
            $kode = 'PRS-' . now()->format('ymd') . '-' . strtoupper(Str::random(5));
        } while (DataPerusahaan::where('kode', $kode)->exists());

        return $kode;
    }

    private function validateFonnteDeviceOrFail(?string $nomorPerusahaan, ?string $tokenApiWa): void
    {
        $result = $this->checkFonnteDevice($nomorPerusahaan, $tokenApiWa);

        /*
         * Status yang boleh simpan:
         * connected     = token valid, nomor sesuai, device connect
         * disconnected  = token valid, nomor sesuai, tetapi device belum connect
         */
        if (in_array($result['status'] ?? null, ['connected', 'disconnected'], true)) {
            return;
        }

        /*
         * Status yang tetap gagal:
         * invalid   = nomor/token tidak valid
         * mismatch  = nomor perusahaan berbeda dengan nomor pada token
         * error     = gagal request / response Fonnte error
         */
        $field = $result['field'] ?? 'token_api_wa';

        throw ValidationException::withMessages([
            $field => $result['message'] ?? 'Validasi token API WA gagal.',
        ]);
    }

    private function checkFonnteDevice(?string $nomorPerusahaan, ?string $tokenApiWa): array
    {
        if (!$nomorPerusahaan) {
            return [
                'success' => false,
                'field' => 'no_wa',
                'status' => 'invalid',
                'label' => 'Tidak Valid',
                'message' => 'Format nomor perusahaan tidak valid. Gunakan format 08xxx, 628xxx, atau +628xxx.',
            ];
        }

        if (!$tokenApiWa) {
            return [
                'success' => false,
                'field' => 'token_api_wa',
                'status' => 'invalid',
                'label' => 'Token Kosong',
                'message' => 'Token API WA wajib diisi.',
            ];
        }

        try {
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Authorization' => $tokenApiWa,
                ])
                ->timeout(30)
                ->post('https://api.fonnte.com/device');

            $json = $response->json();

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'field' => 'token_api_wa',
                    'status' => 'error',
                    'label' => 'Gagal Cek',
                    'message' => 'Gagal mengecek token API WA ke Fonnte.',
                    'fonnte_response' => $json ?: $response->body(),
                ];
            }

            if (!($json['status'] ?? false)) {
                return [
                    'success' => false,
                    'field' => 'token_api_wa',
                    'status' => 'invalid',
                    'label' => 'Tidak Valid',
                    'message' => $json['reason']
                        ?? $json['message']
                        ?? 'Token API WA tidak valid.',
                    'fonnte_response' => $json,
                ];
            }

            $deviceNumber = $this->normalizeWhatsappNumber($json['device'] ?? null);
            $deviceStatus = $json['device_status'] ?? null;

            if (!$deviceNumber) {
                return [
                    'success' => false,
                    'field' => 'token_api_wa',
                    'status' => 'invalid',
                    'label' => 'Tidak Valid',
                    'message' => 'Nomor device pada token API WA tidak ditemukan.',
                    'fonnte_response' => $json,
                ];
            }

            if ($deviceNumber !== $nomorPerusahaan) {
                return [
                    'success' => false,
                    'field' => 'no_wa',
                    'status' => 'mismatch',
                    'label' => 'Nomor Beda',
                    'message' => 'Nomor perusahaan tidak sesuai dengan token API WA. Token ini terdaftar untuk nomor ' . ($json['device'] ?? '-') . '.',
                    'device_number' => $deviceNumber,
                    'device_status' => $deviceStatus,
                    'fonnte_response' => $json,
                ];
            }

            /*
             * Nomor sudah sesuai token, tetapi device belum connect.
             * Untuk validasi manual tetap ditampilkan sebagai belum connect.
             * Untuk proses simpan/update, status ini tetap boleh lewat
             * karena token dan nomor sudah benar.
             */
            if ($deviceStatus !== 'connect') {
                return [
                    'success' => false,
                    'field' => 'token_api_wa',
                    'status' => 'disconnected',
                    'label' => 'Belum Connect',
                    'message' => 'Nomor sesuai token, tetapi device WhatsApp belum connect.',
                    'device_number' => $deviceNumber,
                    'device_status' => $deviceStatus,
                    'fonnte_response' => $json,
                ];
            }

            return [
                'success' => true,
                'field' => null,
                'status' => 'connected',
                'label' => 'Connect',
                'message' => 'Nomor perusahaan dan token API WA valid. Device sudah connect.',
                'device_number' => $deviceNumber,
                'device_status' => $deviceStatus,
                'fonnte_response' => $json,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'field' => 'token_api_wa',
                'status' => 'error',
                'label' => 'Gagal Cek',
                'message' => 'Gagal memvalidasi token API WA: ' . $e->getMessage(),
            ];
        }
    }

    private function normalizeWhatsappNumber(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);
        $value = preg_replace('/[^0-9+]/', '', $value);

        if ($value === '') {
            return null;
        }

        if (Str::startsWith($value, '+')) {
            $value = substr($value, 1);
        }

        if (Str::startsWith($value, '0')) {
            $value = '62' . substr($value, 1);
        }

        if (Str::startsWith($value, '8')) {
            $value = '62' . $value;
        }

        if (!preg_match('/^62[0-9]{8,15}$/', $value)) {
            return null;
        }

        return $value;
    }

    private function normalizePhone(?string $value): ?string
    {
        return $this->normalizeWhatsappNumber($value);
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