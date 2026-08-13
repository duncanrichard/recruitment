<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IntegrationDeliveryService
{
    public function queue(string $provider, array $identity, ?string $companyId = null, int $itemCount = 1): string
    {
        $key = hash('sha256', json_encode([$provider, $identity], JSON_THROW_ON_ERROR));

        DB::table('integration_deliveries')->insertOrIgnore([
            'id' => (string) Str::uuid(),
            'idempotency_key' => $key,
            'provider' => $provider,
            'company_id' => $companyId,
            'status' => 'queued',
            'attempts' => 0,
            'item_count' => $itemCount,
            'payload' => json_encode($identity, JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $key;
    }
}
