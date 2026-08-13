<?php

namespace App\Services;

use App\Models\DataRiwayatDiri;
use Illuminate\Validation\ValidationException;

class RecruitmentWorkflowService
{
    public const STAGES = [
        'registered',
        'administration_passed',
        'zoom_passed',
        'mmpi_passed',
        'interview_passed',
        'accepted',
        'offering_scheduled',
        'completed',
    ];

    public function assertTransition(string $from, string $to): void
    {
        $fromIndex = array_search($from, self::STAGES, true);
        $toIndex = array_search($to, self::STAGES, true);

        if ($fromIndex === false || $toIndex === false || $toIndex !== $fromIndex + 1) {
            throw ValidationException::withMessages([
                'workflow' => "Transisi tahapan {$from} ke {$to} tidak diperbolehkan.",
            ]);
        }
    }

    public function assertCandidateCompany(DataRiwayatDiri $candidate, ?string $companyId): void
    {
        if ((string) $candidate->perusahaan_dilamar !== (string) $companyId) {
            throw ValidationException::withMessages([
                'candidate' => 'Kandidat tidak berasal dari perusahaan yang diizinkan.',
            ]);
        }
    }
}
