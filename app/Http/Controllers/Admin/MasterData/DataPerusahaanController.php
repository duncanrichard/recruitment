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
        $wahaStatus = $this->checkWahaSession();

        $data = DataPerusahaan::query()
            ->orderBy('nama_perusahaan', 'asc')
            ->get()
            ->map(function ($item) use ($wahaStatus) {
                $nomorPerusahaan = $this->normalizeWhatsappNumber($item->no_wa ?? null);

                $statusWa = $this->buildCompanyWaStatus($nomorPerusahaan, $wahaStatus);

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
        ], [
            'nama_perusahaan.required' => 'Nama perusahaan wajib diisi.',
            'nama_perusahaan.unique' => 'Nama perusahaan sudah digunakan.',
            'no_wa.required' => 'Nomor perusahaan wajib diisi.',
            'no_wa.max' => 'Nomor perusahaan maksimal 30 karakter.',
        ]);

        $nomorPerusahaan = $this->normalizeWhatsappNumber($validated['no_wa']);

        if (!$nomorPerusahaan) {
            throw ValidationException::withMessages([
                'no_wa' => 'Format nomor perusahaan tidak valid. Gunakan format 08xxx, 628xxx, atau +628xxx.',
            ]);
        }

        $perusahaan = DataPerusahaan::create([
            'kode' => $this->generateKodePerusahaan(),
            'nama_perusahaan' => trim($validated['nama_perusahaan']),
            'no_wa' => $nomorPerusahaan,

            // Token tidak dipakai lagi karena WAHA pakai WAHA_API_KEY dari .env
            'token_api_wa' => null,

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
        ], [
            'nama_perusahaan.required' => 'Nama perusahaan wajib diisi.',
            'nama_perusahaan.unique' => 'Nama perusahaan sudah digunakan.',
            'no_wa.required' => 'Nomor perusahaan wajib diisi.',
            'no_wa.max' => 'Nomor perusahaan maksimal 30 karakter.',
        ]);

        $nomorPerusahaan = $this->normalizeWhatsappNumber($validated['no_wa']);

        if (!$nomorPerusahaan) {
            throw ValidationException::withMessages([
                'no_wa' => 'Format nomor perusahaan tidak valid. Gunakan format 08xxx, 628xxx, atau +628xxx.',
            ]);
        }

        $payload = [
            'nama_perusahaan' => trim($validated['nama_perusahaan']),
            'no_wa' => $nomorPerusahaan,

            // Token lama dikosongkan karena sudah pindah ke WAHA global
            'token_api_wa' => null,
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
        $wahaStatus = $this->checkWahaSession();
        $result = $this->buildCompanyWaStatus($nomorPerusahaan, $wahaStatus);

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
                'waha_response' => $result['waha_response'] ?? null,
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

    private function buildCompanyWaStatus(?string $nomorPerusahaan, array $wahaStatus): array
    {
        if (!$nomorPerusahaan) {
            return [
                'success' => false,
                'field' => 'no_wa',
                'status' => 'invalid',
                'label' => 'Tidak Valid',
                'message' => 'Format nomor perusahaan tidak valid. Gunakan format 08xxx, 628xxx, atau +628xxx.',
                'device_number' => $wahaStatus['device_number'] ?? null,
                'device_status' => $wahaStatus['device_status'] ?? null,
                'waha_response' => $wahaStatus['waha_response'] ?? null,
            ];
        }

        if (!($wahaStatus['success'] ?? false)) {
            return [
                'success' => false,
                'field' => 'no_wa',
                'status' => $wahaStatus['status'] ?? 'error',
                'label' => $wahaStatus['label'] ?? 'Gagal Cek',
                'message' => $wahaStatus['message'] ?? 'WAHA belum connect.',
                'device_number' => $wahaStatus['device_number'] ?? null,
                'device_status' => $wahaStatus['device_status'] ?? null,
                'waha_response' => $wahaStatus['waha_response'] ?? null,
            ];
        }

        $deviceNumber = $this->normalizeWhatsappNumber($wahaStatus['device_number'] ?? null);

        if ($deviceNumber && $deviceNumber !== $nomorPerusahaan) {
            return [
                'success' => false,
                'field' => 'no_wa',
                'status' => 'mismatch',
                'label' => 'Nomor Beda',
                'message' => 'Nomor perusahaan berbeda dengan nomor WAHA. Nomor WAHA aktif: ' . $deviceNumber . '.',
                'device_number' => $deviceNumber,
                'device_status' => $wahaStatus['device_status'] ?? null,
                'waha_response' => $wahaStatus['waha_response'] ?? null,
            ];
        }

        return [
            'success' => true,
            'field' => null,
            'status' => 'connected',
            'label' => 'Connect',
            'message' => 'WAHA sudah connect dan nomor perusahaan sesuai.',
            'device_number' => $deviceNumber,
            'device_status' => $wahaStatus['device_status'] ?? null,
            'waha_response' => $wahaStatus['waha_response'] ?? null,
        ];
    }

   private function checkWahaSession(): array
{
    $baseUrl = rtrim(config('services.waha.url', env('WAHA_URL', 'https://wa.blast.dsicorp.id')), '/');
    $session = config('services.waha.session', env('WAHA_SESSION', 'rekruitment'));
    $apiKey = config('services.waha.api_key', env('WAHA_API_KEY'));

    $headers = [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
    ];

    if (!empty($apiKey)) {
        $headers['X-Api-Key'] = $apiKey;
    }

    try {
        /*
         * Pakai /api/sessions saja.
         * Endpoint ini sudah terbukti berhasil dari curl kamu.
         * Jangan pakai /api/sessions/{session}, karena bisa beda support tergantung versi WAHA/OpenWA.
         */
        $response = Http::withoutVerifying()
            ->withHeaders($headers)
            ->timeout(30)
            ->get($baseUrl . '/api/sessions');

        $json = $response->json();

        if (!$response->successful()) {
            return [
                'success' => false,
                'status' => 'error',
                'label' => 'Gagal Cek',
                'message' => 'Gagal mengecek session WAHA. HTTP Code: ' . $response->status(),
                'device_number' => null,
                'device_status' => null,
                'waha_response' => $json ?: $response->body(),
            ];
        }

        $sessionData = $this->extractWahaSessionData($json, $session);

        if (empty($sessionData)) {
            return [
                'success' => false,
                'status' => 'not_found',
                'label' => 'Session Tidak Ada',
                'message' => 'Session WAHA "' . $session . '" tidak ditemukan. Pastikan WAHA_SESSION di .env sama dengan nama session OpenWA.',
                'device_number' => null,
                'device_status' => null,
                'waha_response' => $json,
            ];
        }

        $deviceStatus = strtolower((string) ($sessionData['status'] ?? $sessionData['device_status'] ?? ''));
        $deviceNumber = $this->extractWahaPhoneNumber($sessionData);

        $isConnected = in_array($deviceStatus, [
            'connected',
            'connect',
            'working',
            'authenticated',
            'ready',
        ], true);

        if (!$isConnected) {
            return [
                'success' => false,
                'status' => 'disconnected',
                'label' => 'Belum Connect',
                'message' => 'Session WAHA belum connect. Status saat ini: ' . ($deviceStatus ?: '-'),
                'device_number' => $deviceNumber,
                'device_status' => $deviceStatus ?: null,
                'waha_response' => $json,
            ];
        }

        return [
            'success' => true,
            'status' => 'connected',
            'label' => 'Connect',
            'message' => 'Session WAHA sudah connect.',
            'device_number' => $deviceNumber,
            'device_status' => $deviceStatus,
            'waha_response' => $json,
        ];
    } catch (\Throwable $e) {
        return [
            'success' => false,
            'status' => 'error',
            'label' => 'Gagal Cek',
            'message' => 'Gagal memvalidasi WAHA: ' . $e->getMessage(),
            'device_number' => null,
            'device_status' => null,
        ];
    }
}

    private function extractWahaSessionData($json, string $session): array
    {
        if (is_array($json) && array_is_list($json)) {
            foreach ($json as $item) {
                if (($item['name'] ?? null) === $session || ($item['session'] ?? null) === $session) {
                    return is_array($item) ? $item : [];
                }
            }

            return is_array($json[0] ?? null) ? $json[0] : [];
        }

        return is_array($json) ? $json : [];
    }

    private function extractWahaPhoneNumber(array $sessionData): ?string
    {
        $candidates = [
            $sessionData['phone'] ?? null,
            $sessionData['phoneNumber'] ?? null,
            $sessionData['phone_number'] ?? null,
            $sessionData['me']['id'] ?? null,
            $sessionData['me']['user'] ?? null,
            $sessionData['me']['number'] ?? null,
            $sessionData['me']['phone'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            $number = $this->normalizeWhatsappNumber($candidate);

            if ($number) {
                return $number;
            }
        }

        return null;
    }

    private function normalizeWhatsappNumber(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        // Hapus suffix WAHA seperti @c.us
        $value = preg_replace('/@.*/', '', $value);

        // Ambil hanya angka dan +
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
}