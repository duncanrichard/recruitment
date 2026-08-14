<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class NineRouterService
{
    public function analyze(array $candidate, string $task): array
    {
        $model = trim((string) config('services.ninerouter.model'));

        if ($model === '') {
            throw new RuntimeException('NINEROUTER_MODEL belum dikonfigurasi. Isi dengan nama model yang tersedia di 9Router.');
        }

        $instructions = match ($task) {
            'interview_questions' => 'Susun pertanyaan interview yang relevan dan alasan setiap pertanyaan.',
            'data_review' => 'Temukan data profesional yang belum lengkap, tidak konsisten, atau perlu dikonfirmasi HR.',
            default => 'Ringkas kecocokan kandidat dengan posisi secara objektif, termasuk kekuatan, gap, dan tindak lanjut.',
        };

        try {
            $response = $this->request()->post($this->endpoint('/chat/completions'), [
                'model' => $model,
                'temperature' => 0.2,
                'max_completion_tokens' => 1400,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' => 'Anda adalah asisten HR Indonesia. Gunakan hanya data yang diberikan. Jangan mengarang fakta, jangan membuat keputusan lolos/gagal, dan jangan menggunakan atribut sensitif. Balas JSON valid dengan properti: summary (string), strengths (array string), gaps (array string), follow_up (array string), disclaimer (string).'],
                    ['role' => 'user', 'content' => $instructions."\n\nData profesional kandidat:\n".json_encode($candidate, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)],
                ],
            ]);
        } catch (ConnectionException $exception) {
            throw new RuntimeException('9Router tidak dapat dihubungi. Pastikan 9Router aktif dan endpoint lokal dapat diakses.', previous: $exception);
        }

        if (! $response->successful()) {
            if ($response->status() === 401) {
                throw new RuntimeException('API key 9Router belum valid. Salin API key dari menu Endpoint & Key 9Router ke NINEROUTER_API_KEY pada file .env, lalu bersihkan cache konfigurasi.');
            }

            $message = $response->json('error.message') ?: '9Router menolak permintaan analisis (HTTP '.$response->status().').';
            throw new RuntimeException($message);
        }

        $content = $response->json('choices.0.message.content');
        $decoded = is_string($content) ? json_decode($content, true) : null;

        if (! is_array($decoded)) {
            throw new RuntimeException('Respons AI dari 9Router tidak memiliki format JSON yang dapat diproses.');
        }

        return [
            'model' => $response->json('model', $model),
            'summary' => (string) ($decoded['summary'] ?? ''),
            'strengths' => array_values(array_filter((array) ($decoded['strengths'] ?? []), 'is_string')),
            'gaps' => array_values(array_filter((array) ($decoded['gaps'] ?? []), 'is_string')),
            'follow_up' => array_values(array_filter((array) ($decoded['follow_up'] ?? []), 'is_string')),
            'disclaimer' => (string) ($decoded['disclaimer'] ?? 'Hasil AI merupakan rekomendasi. Keputusan akhir tetap dilakukan oleh HR.'),
            'usage' => $response->json('usage'),
        ];
    }

    private function request(): PendingRequest
    {
        $request = Http::asJson()->acceptJson()
            ->connectTimeout((int) config('services.ninerouter.connect_timeout', 5))
            ->timeout((int) config('services.ninerouter.timeout', 60))
            ->retry(2, 500, throw: false);
        $apiKey = trim((string) config('services.ninerouter.api_key'));

        return $apiKey !== '' ? $request->withToken($apiKey) : $request;
    }

    private function endpoint(string $path): string
    {
        return rtrim((string) config('services.ninerouter.base_url'), '/').'/'.ltrim($path, '/');
    }
}
