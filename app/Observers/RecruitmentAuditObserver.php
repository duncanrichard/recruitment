<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class RecruitmentAuditObserver
{
    private const HIDDEN = [
        'password',
        'remember_token',
        'token',
        'token_hash',
        'token_ciphertext',
        'token_api_wa',
    ];

    public function created(Model $model): void
    {
        $this->record($model, 'created', [], $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        $changes = $model->getChanges();
        unset($changes['updated_at']);

        if ($changes === []) {
            return;
        }

        $old = collect(array_keys($changes))
            ->mapWithKeys(fn ($key) => [$key => $model->getOriginal($key)])
            ->all();

        $this->record($model, 'updated', $old, $changes);
    }

    public function deleted(Model $model): void
    {
        $this->record($model, 'deleted', $model->getOriginal(), []);
    }

    private function record(Model $model, string $event, array $old, array $new): void
    {
        if (! Schema::hasTable('recruitment_audits')) {
            return;
        }

        $request = app()->runningInConsole() ? null : request();

        $payload = [
            'id' => (string) Str::uuid(),
            'auditable_type' => $model::class,
            'auditable_id' => (string) $model->getKey(),
            'event' => $event,
            'user_id' => Auth::user()?->uuid,
            'old_values' => $this->safeJson($old),
            'new_values' => $this->safeJson($new),
            'ip_address' => $request?->ip(),
            'user_agent' => Str::limit((string) $request?->userAgent(), 1000, ''),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('recruitment_audits', 'company_id')) {
            $payload['company_id'] = $this->companyId($model);
            $payload['request_id'] = $request?->attributes->get('request_id');
            $payload['route_name'] = $request?->route()?->getName();
        }

        DB::table('recruitment_audits')->insert($payload);
    }

    private function safeJson(array $values): ?string
    {
        $safe = collect($values)->except(self::HIDDEN)->all();

        return $safe === [] ? null : json_encode($safe, JSON_THROW_ON_ERROR);
    }

    private function companyId(Model $model): ?string
    {
        foreach (['perusahaan_dilamar', 'company_id', 'perusahaan_id'] as $attribute) {
            $value = $model->getAttribute($attribute);
            if (! empty($value)) {
                return (string) $value;
            }
        }

        $candidate = null;
        foreach (['kandidat', 'dataRiwayatDiri'] as $relation) {
            if (method_exists($model, $relation)) {
                $candidate = $model->{$relation}()->first();
                break;
            }
        }

        if (! $candidate && method_exists($model, 'hasilInterview')) {
            $candidate = $model->hasilInterview()->first()?->kandidat()->first();
        }

        if (! $candidate && method_exists($model, 'hasilReviewManagement')) {
            $candidate = $model->hasilReviewManagement()->first()
                ?->hasilInterview()->first()
                ?->kandidat()->first();
        }

        return $candidate?->perusahaan_dilamar
            ? (string) $candidate->perusahaan_dilamar
            : null;
    }
}
