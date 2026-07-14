<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\DataPerusahaan;
use Illuminate\Http\Client\ConnectionException;
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
            ->orderBy('nama_perusahaan')
            ->get()
            ->map(function (DataPerusahaan $item) {
                $status = $this->checkFonnteDevice($item->token_api_wa);

                $item->setAttribute('wa_status', $status['status']);
                $item->setAttribute('wa_status_label', $status['label']);
                $item->setAttribute('wa_status_message', $status['message']);
                $item->setAttribute('wa_device_number', $status['device_number']);
                $item->setAttribute('wa_device_status', $status['device_status']);

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
        $validated = $this->validatePayload($request);

        $nomorPerusahaan = $this->normalizeWhatsappNumber($validated['no_wa']);

        if (!$nomorPerusahaan) {
            throw ValidationException::withMessages([
                'no_wa' => 'Format nomor WhatsApp tidak valid. Gunakan 08xxx, 628xxx, atau +628xxx.',
            ]);
        }

        $perusahaan = DataPerusahaan::create([
            'kode' => $this->generateKodePerusahaan(),
            'nama_perusahaan' => trim($validated['nama_perusahaan']),
            'no_wa' => $nomorPerusahaan,
            'token_api_wa' => trim($validated['token_api_wa']),
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
        $validated = $this->validatePayload($request, $perusahaan);

        $nomorPerusahaan = $this->normalizeWhatsappNumber($validated['no_wa']);

        if (!$nomorPerusahaan) {
            throw ValidationException::withMessages([
                'no_wa' => 'Format nomor WhatsApp tidak valid. Gunakan 08xxx, 628xxx, atau +628xxx.',
            ]);
        }

        $payload = [
            'nama_perusahaan' => trim($validated['nama_perusahaan']),
            'no_wa' => $nomorPerusahaan,
        ];

        if (!empty($validated['token_api_wa'])) {
            $payload['token_api_wa'] = trim($validated['token_api_wa']);
        }

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
            $perusahaan->update(['deleted_by' => Auth::id()]);
        }

        $perusahaan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data perusahaan berhasil dihapus.',
        ]);
    }

    public function validasiFonnte(string $id): JsonResponse
    {
        $perusahaan = DataPerusahaan::findOrFail($id);
        $result = $this->checkFonnteDevice($perusahaan->token_api_wa);

        $nomorDatabase = $this->normalizeWhatsappNumber($perusahaan->no_wa);
        $nomorDevice = $this->normalizeWhatsappNumber($result['device_number']);

        if ($result['success'] && $nomorDevice && $nomorDatabase !== $nomorDevice) {
            $result = [
                ...$result,
                'success' => false,
                'status' => 'mismatch',
                'label' => 'Nomor Beda',
                'message' => "Token Fonnte terhubung ke {$nomorDevice}, sedangkan nomor perusahaan {$nomorDatabase}.",
            ];
        }

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'data' => [
                'perusahaan_id' => $perusahaan->id,
                'nama_perusahaan' => $perusahaan->nama_perusahaan,
                'nomor_database' => $nomorDatabase,
                'nomor_device' => $nomorDevice,
                'device_status' => $result['device_status'],
                'wa_status' => $result['status'],
                'wa_status_label' => $result['label'],
                'wa_status_message' => $result['message'],
                'fonnte_response' => $result['response'],
            ],
        ], $result['success'] ? 200 : 422);
    }

    private function validatePayload(
        Request $request,
        ?DataPerusahaan $perusahaan = null
    ): array {
        return $request->validate([
            'nama_perusahaan' => [
                'required',
                'string',
                'max:255',
                Rule::unique('data_perusahaan', 'nama_perusahaan')
                    ->ignore($perusahaan?->id, 'id')
                    ->whereNull('deleted_at'),
            ],
            'no_wa' => ['required', 'string', 'max:30'],
            'token_api_wa' => [
                $perusahaan ? 'nullable' : 'required',
                'string',
                'max:500',
            ],
        ], [
            'nama_perusahaan.required' => 'Nama perusahaan wajib diisi.',
            'nama_perusahaan.unique' => 'Nama perusahaan sudah digunakan.',
            'no_wa.required' => 'Nomor WhatsApp wajib diisi.',
            'token_api_wa.required' => 'Token API Fonnte wajib diisi.',
        ]);
    }

    private function checkFonnteDevice(?string $token): array
    {
        if (!$token || trim($token) === '') {
            return $this->fonnteResult(
                false,
                'token_empty',
                'Token Kosong',
                'Token API Fonnte belum diisi.'
            );
        }

        try {
            $deviceUrl = env(
                'FONNTE_DEVICE_URL',
                'https://api.fonnte.com/device'
            );

            $connectTimeout = (int) env('FONNTE_CONNECT_TIMEOUT', 10);
            $timeout = (int) env('FONNTE_TIMEOUT', 20);

            $response = Http::withoutVerifying()
                ->withOptions([
                    'verify' => false,
                ])
                ->acceptJson()
                ->withHeaders([
                    'Authorization' => trim($token),
                ])
                ->connectTimeout($connectTimeout)
                ->timeout($timeout)
                ->post($deviceUrl);

            $json = $response->json();
            $payload = is_array($json) ? $json : [];

            if (!$response->successful()) {
                $message = $response->status() === 405
                    ? 'Metode request Fonnte ditolak. Endpoint /device harus dipanggil menggunakan POST.'
                    : (
                        $payload['reason']
                        ?? $payload['message']
                        ?? 'Fonnte mengembalikan HTTP ' . $response->status() . '.'
                    );

                return $this->fonnteResult(
                    false,
                    'error',
                    'Gagal Cek',
                    $message,
                    null,
                    null,
                    $payload ?: $response->body()
                );
            }

            $apiSuccess = filter_var(
                $payload['status'] ?? $payload['success'] ?? false,
                FILTER_VALIDATE_BOOLEAN
            );

            $deviceNumber = $this->extractFonnteNumber($payload);
            $deviceStatus = strtolower((string) (
                $payload['device_status']
                ?? $payload['deviceStatus']
                ?? $payload['connection']
                ?? $payload['status_device']
                ?? ''
            ));

            $connectedStatuses = [
                'connect',
                'connected',
                'ready',
                'online',
                'authenticated',
                'working',
            ];

            /*
             * Beberapa response Fonnte hanya memberikan status=true dan nomor
             * device tanpa field device_status. Kondisi tersebut tetap dianggap
             * terhubung.
             */
            $connected = $apiSuccess && (
                in_array($deviceStatus, $connectedStatuses, true)
                || ($deviceStatus === '' && $deviceNumber !== null)
            );

            if (!$connected) {
                $message = (string) (
                    $payload['reason']
                    ?? $payload['message']
                    ?? $payload['detail']
                    ?? 'Device Fonnte belum terhubung.'
                );

                return $this->fonnteResult(
                    false,
                    'disconnected',
                    'Belum Connect',
                    $message,
                    $deviceNumber,
                    $deviceStatus ?: null,
                    $payload
                );
            }

            return $this->fonnteResult(
                true,
                'connected',
                'Connect',
                'Token Fonnte valid dan device sudah terhubung.',
                $deviceNumber,
                $deviceStatus ?: 'connected',
                $payload
            );
        } catch (ConnectionException $e) {
            return $this->fonnteResult(
                false,
                'error',
                'Koneksi Gagal',
                'Tidak dapat terhubung ke API Fonnte: ' . $e->getMessage()
            );
        } catch (\Throwable $e) {
            report($e);

            return $this->fonnteResult(
                false,
                'error',
                'Gagal Cek',
                'Gagal memvalidasi Fonnte: ' . $e->getMessage()
            );
        }
    }

    private function fonnteResult(
        bool $success,
        string $status,
        string $label,
        string $message,
        ?string $deviceNumber = null,
        ?string $deviceStatus = null,
        mixed $response = null
    ): array {
        return [
            'success' => $success,
            'status' => $status,
            'label' => $label,
            'message' => $message,
            'device_number' => $deviceNumber,
            'device_status' => $deviceStatus,
            'response' => $response,
        ];
    }

    private function extractFonnteNumber(array $payload): ?string
    {
        $candidates = [
            $payload['device'] ?? null,
            $payload['number'] ?? null,
            $payload['phone'] ?? null,
            $payload['phone_number'] ?? null,
            $payload['data']['device'] ?? null,
            $payload['data']['number'] ?? null,
            $payload['data']['phone'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            $number = $this->normalizeWhatsappNumber(
                is_scalar($candidate) ? (string) $candidate : null
            );

            if ($number) {
                return $number;
            }
        }

        return null;
    }

    private function generateKodePerusahaan(): string
    {
        do {
            $kode = 'PRS-' . now()->format('ymd') . '-' . strtoupper(Str::random(5));
        } while (DataPerusahaan::where('kode', $kode)->exists());

        return $kode;
    }

    private function normalizeWhatsappNumber(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = preg_replace('/@.*/', '', trim($value));
        $value = preg_replace('/[^0-9+]/', '', $value);

        if (!$value) {
            return null;
        }

        $value = ltrim($value, '+');

        if (Str::startsWith($value, '0')) {
            $value = '62' . substr($value, 1);
        } elseif (Str::startsWith($value, '8')) {
            $value = '62' . $value;
        }

        return preg_match('/^62[0-9]{8,15}$/', $value) ? $value : null;
    }
}
