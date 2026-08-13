<?php

namespace App\Policies;

use App\Models\DataRiwayatDiri;
use App\Models\User;
use App\Services\CompanyAccessService;

class DataRiwayatDiriPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasAnyRole(['Superadmin', 'Super Admin', 'superadmin', 'super admin'])
            ? true
            : null;
    }

    public function view(User $user, DataRiwayatDiri $candidate): bool
    {
        return app(CompanyAccessService::class)->canAccess(
            $user,
            $candidate->perusahaan_dilamar
        );
    }

    public function update(User $user, DataRiwayatDiri $candidate): bool
    {
        return $this->view($user, $candidate);
    }

    public function delete(User $user, DataRiwayatDiri $candidate): bool
    {
        return $this->view($user, $candidate);
    }

    public function downloadDocument(User $user, DataRiwayatDiri $candidate): bool
    {
        return $this->view($user, $candidate)
            && $user->can('admin.data-pelamar.download-document');
    }
}
