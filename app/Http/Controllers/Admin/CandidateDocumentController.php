<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataRiwayatDiri;
use App\Models\JadwalInterviewKandidat;
use App\Services\RecruitmentAuditService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CandidateDocumentController extends Controller
{
    use AuthorizesRequests;

    public function show(DataRiwayatDiri $candidate, string $type): StreamedResponse
    {
        $this->authorize('downloadDocument', $candidate);
        abort_unless(in_array($type, ['cv', 'foto'], true), 404);

        $column = $type === 'cv' ? 'file_cv' : 'file_foto';
        $row = JadwalInterviewKandidat::query()
            ->where('data_riwayat_diri_id', $candidate->id)
            ->whereNotNull($column)
            ->latest('updated_at')
            ->firstOrFail();

        $path = $this->storagePath((string) $row->{$column});
        $disk = Storage::disk(config('filesystems.default'));
        abort_unless($path !== '' && $disk->exists($path), 404);

        app(RecruitmentAuditService::class)->record(
            'candidate_document',
            (string) $row->id,
            'downloaded',
            ['candidate_id' => $candidate->id, 'type' => $type, 'path_hash' => hash('sha256', $path)]
        );

        return $disk->download($path, basename($path));
    }

    private function storagePath(string $value): string
    {
        if (Str::startsWith($value, ['http://', 'https://'])) {
            $value = (string) parse_url($value, PHP_URL_PATH);
        }

        return ltrim(Str::after($value, '/storage/'), '/');
    }
}
