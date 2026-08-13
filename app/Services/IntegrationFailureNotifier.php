<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class IntegrationFailureNotifier
{
    public function notify(string $provider, string $deliveryId, ?\Throwable $exception): void
    {
        $message = Str::limit((string) $exception?->getMessage(), 2000, '');

        Log::critical('Integrasi recruitment gagal permanen', [
            'provider' => $provider,
            'delivery_id' => $deliveryId,
            'error' => $message,
        ]);

        app(RecruitmentAuditService::class)->record(
            'integration_delivery',
            $deliveryId,
            'failed_permanently',
            ['provider' => $provider, 'error' => $message]
        );
    }
}
