<?php

namespace App\Jobs;

use App\Models\DataPerusahaan;
use App\Services\IntegrationFailureNotifier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class SendFonnteBatch implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 150;

    public array $backoff = [30, 120, 300];

    public int $uniqueFor = 600;

    public function __construct(
        public readonly string $companyId,
        public readonly array $messages,
        public readonly string $idempotencyKey,
    ) {
        $this->onQueue('notifications');
    }

    public function uniqueId(): string
    {
        return $this->idempotencyKey;
    }

    public function handle(): void
    {
        $delivery = DB::table('integration_deliveries')
            ->where('idempotency_key', $this->idempotencyKey)
            ->first();

        if ($delivery?->status === 'delivered') {
            return;
        }

        $company = DataPerusahaan::query()->findOrFail($this->companyId);
        $token = trim((string) $company->token_api_wa);

        if ($token === '') {
            throw new RuntimeException('Token API Fonnte perusahaan tidak tersedia.');
        }

        DB::table('integration_deliveries')
            ->where('idempotency_key', $this->idempotencyKey)
            ->update([
                'status' => 'processing',
                'attempts' => DB::raw('attempts + 1'),
                'updated_at' => now(),
            ]);

        $response = Http::asForm()
            ->withHeaders(['Authorization' => $token])
            ->connectTimeout(10)
            ->timeout(120)
            ->retry(2, 500)
            ->post('https://api.fonnte.com/send', [
                'data' => json_encode($this->messages, JSON_THROW_ON_ERROR),
                'countryCode' => '62',
                'typing' => 'false',
                'preview' => 'true',
            ]);

        $body = $response->json() ?: ['body' => Str::limit($response->body(), 5000)];
        $success = $response->successful() && (bool) ($body['status'] ?? $body['Status'] ?? false);

        DB::table('integration_deliveries')
            ->where('idempotency_key', $this->idempotencyKey)
            ->update([
                'status' => $success ? 'delivered' : 'failed',
                'http_status' => $response->status(),
                'provider_response' => json_encode($body, JSON_THROW_ON_ERROR),
                'error_message' => $success ? null : ($body['reason'] ?? $body['message'] ?? 'Provider menolak pengiriman.'),
                'delivered_at' => $success ? now() : null,
                'updated_at' => now(),
            ]);

        if (! $success) {
            throw new RuntimeException('Pengiriman batch Fonnte gagal.');
        }
    }

    public function failed(?\Throwable $exception): void
    {
        DB::table('integration_deliveries')
            ->where('idempotency_key', $this->idempotencyKey)
            ->update([
                'status' => 'failed',
                'error_message' => Str::limit((string) $exception?->getMessage(), 2000, ''),
                'updated_at' => now(),
            ]);

        app(IntegrationFailureNotifier::class)->notify('fonnte', $this->idempotencyKey, $exception);
    }
}
