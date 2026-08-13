<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class CompanyAccessService
{
    public function canAccess(?User $user, string|int|null $companyId): bool
    {
        if ($companyId === null || $companyId === '') {
            return false;
        }

        $ids = $this->allowedCompanyIds($user);

        return $ids === null || in_array((string) $companyId, $ids, true);
    }

    public function allowedCompanyIds(?User $user): ?array
    {
        if (! $user) {
            return [];
        }

        if ($user->hasAnyRole(['superadmin', 'Superadmin', 'super admin', 'Super Admin'])) {
            return null;
        }

        $ids = $user->perusahaans()
            ->pluck('data_perusahaan.id')
            ->map(fn ($id) => (string) $id)
            ->all();

        if (! empty($user->perusahaan_id)) {
            $ids[] = (string) $user->perusahaan_id;
        }

        return collect($ids)->filter()->unique()->values()->all();
    }

    public function apply(
        EloquentBuilder|QueryBuilder $query,
        ?User $user,
        string $companyColumn
    ): EloquentBuilder|QueryBuilder {
        $ids = $this->allowedCompanyIds($user);

        if ($ids === null) {
            return $query;
        }

        if ($ids === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn($companyColumn, $ids);
    }
}
