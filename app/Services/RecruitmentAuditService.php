<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class RecruitmentAuditService
{
    public function record(string $type, string $id, string $event, array $metadata = [], ?string $companyId = null): void
    {
        if (! Schema::hasTable('recruitment_audits')) {
            return;
        }

        $request = app()->runningInConsole() ? null : request();

        $payload = [
            'id' => (string) Str::uuid(),
            'auditable_type' => $type,
            'auditable_id' => $id,
            'event' => $event,
            'user_id' => Auth::user()?->uuid,
            'old_values' => null,
            'new_values' => $metadata === [] ? null : json_encode($metadata, JSON_THROW_ON_ERROR),
            'ip_address' => $request?->ip(),
            'user_agent' => Str::limit((string) $request?->userAgent(), 1000, ''),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('recruitment_audits', 'company_id')) {
            $allowedCompanyIds = app(CompanyAccessService::class)->allowedCompanyIds(Auth::user());
            $inferredCompanyId = is_array($allowedCompanyIds) && count($allowedCompanyIds) === 1
                ? $allowedCompanyIds[0]
                : null;
            $payload['company_id'] = $companyId ?? ($metadata['company_id'] ?? $inferredCompanyId);
            $payload['request_id'] = $request?->attributes->get('request_id');
            $payload['route_name'] = $request?->route()?->getName();
        }

        DB::table('recruitment_audits')->insert($payload);
    }
}
