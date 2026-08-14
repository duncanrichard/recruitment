<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataRiwayatDiri;
use App\Services\CompanyAccessService;
use App\Services\NineRouterService;
use App\Services\RecruitmentAuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use RuntimeException;

class AiRecruitmentController extends Controller
{
    public function candidates(Request $request, CompanyAccessService $companies): JsonResponse
    {
        $query = DataRiwayatDiri::query()
            ->with(['posisi:id,nama_posisi', 'perusahaan:id,nama_perusahaan'])
            ->select(['id', 'nama_lengkap', 'posisi_yang_dilamar', 'perusahaan_dilamar', 'tanggal_skrining']);

        $companies->apply($query, Auth::user(), 'data_riwayat_diri.perusahaan_dilamar');

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhereHas('posisi', fn ($position) => $position->where('nama_posisi', 'like', "%{$search}%"));
            });
        }

        return response()->json([
            'success' => true,
            'data' => $query->latest('tanggal_skrining')->limit(100)->get()->map(fn ($candidate) => [
                'id' => $candidate->id,
                'name' => $candidate->nama_lengkap ?: 'Kandidat',
                'position' => $candidate->posisi?->nama_posisi ?: '-',
                'company' => $candidate->perusahaan?->nama_perusahaan ?: '-',
            ]),
        ]);
    }

    public function analyze(
        Request $request,
        CompanyAccessService $companies,
        NineRouterService $nineRouter,
        RecruitmentAuditService $audit
    ): JsonResponse {
        $validated = $request->validate([
            'candidate_id' => ['required', 'uuid', Rule::exists('data_riwayat_diri', 'id')],
            'task' => ['required', Rule::in(['candidate_summary', 'interview_questions', 'data_review'])],
        ]);

        $query = DataRiwayatDiri::query()->with([
            'posisi',
            'pendidikan',
            'riwayatPekerjaan',
            'kesiapanBekerja',
        ]);
        $companies->apply($query, Auth::user(), 'data_riwayat_diri.perusahaan_dilamar');
        $candidate = $query->findOrFail($validated['candidate_id']);

        $professionalData = [
            'candidate_reference' => 'Kandidat '.Str::upper(Str::substr((string) $candidate->id, 0, 8)),
            'position' => $candidate->posisi?->nama_posisi,
            'education' => $candidate->pendidikan?->pendidikan ?? $candidate->pendidikan?->nama,
            'major' => $candidate->jurusan,
            'institution' => $candidate->nama_institusi,
            'work_history' => $candidate->riwayatPekerjaan->map(fn ($job) => collect($job->toArray())->only([
                'nama_perusahaan',
                'posisi_pekerjaan',
                'posisi_pekerjaan_terakhir',
                'bidang_pekerjaan',
                'periode_kerja_awal',
                'periode_kerja_akhir',
                'lama_bekerja',
                'deskripsi_pekerjaan',
                'alasan_berhenti',
                'keahlian',
            ])->filter()->all())->values()->all(),
            'work_readiness' => $candidate->kesiapanBekerja
                ? collect($candidate->kesiapanBekerja->toArray())->only([
                    'kapan_siap_bekerja',
                    'tanggal_siap_kerja',
                    'ekpetasi_gaji',
                    'gaji_diharapkan',
                    'penempatan',
                    'bersedia_shift',
                    'bersedia_training',
                    'bersedia_pelatihan',
                    'alasan_melamar',
                ])->filter()->all()
                : [],
        ];

        try {
            $result = $nineRouter->analyze($professionalData, $validated['task']);
        } catch (RuntimeException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 503);
        }

        $audit->record(
            DataRiwayatDiri::class,
            (string) $candidate->id,
            'ai_analyzed',
            [
                'company_id' => $candidate->perusahaan_dilamar,
                'task' => $validated['task'],
                'model' => $result['model'],
            ],
            (string) $candidate->perusahaan_dilamar
        );

        return response()->json([
            'success' => true,
            'message' => 'Analisis AI berhasil dibuat.',
            'data' => $result,
        ]);
    }
}
