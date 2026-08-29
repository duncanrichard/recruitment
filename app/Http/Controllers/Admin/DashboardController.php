<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CompanyAccessService;
use App\Services\NineRouterService;
use App\Services\RecruitmentAuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

class DashboardController extends Controller
{
    private array $dashboardFilters = [];

    public function index()
    {
        return view('pages.admin.index');
    }

    public function summary(Request $request): JsonResponse
    {
        $this->dashboardFilters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'company_id' => ['nullable', 'uuid'],
            'position_id' => ['nullable', 'uuid'],
            'source_id' => ['nullable', 'uuid'],
        ]);

        $pelamarIds = $this->getPelamarIds();
        $totalPelamar = $pelamarIds->count();

        $jadwalZoomIds = $this->getIdsFromTable('jadwal_test_zoom');
        $hasilZoom = $this->getHasilByPelamar('daftar_hadir_test_zoom');

        $jadwalMmpiIds = $this->getIdsFromTable('jadwal_test_mmpi');
        $hasilMmpi = $this->getHasilByPelamar('daftar_hadir_test_mmpi');

        $interview = $this->getInterviewByPelamar();

        $stageCounts = [
            'administrasi' => 0,
            'jadwal_test_zoom' => 0,
            'hasil_test_zoom_lolos' => 0,
            'hasil_test_zoom_gagal' => 0,
            'jadwal_test_mmpi' => 0,
            'hasil_test_mmpi_lolos' => 0,
            'hasil_test_mmpi_gagal' => 0,
            'jadwal_interview' => 0,
            'interview_reschedule' => 0,
            'interview_lolos' => 0,
            'interview_tidak_lolos' => 0,
            'interview_dipertimbangkan' => 0,
        ];

        foreach ($pelamarIds as $pelamarId) {
            $interviewRow = $interview->get($pelamarId);
            $hasilInterview = $this->normalizeHasilInterview($interviewRow->hasil_interview ?? null);
            $statusInterview = $this->normalizeStatusKehadiran($interviewRow->status_kehadiran ?? null);

            if ($hasilInterview === 'lolos') {
                $stageCounts['interview_lolos']++;

                continue;
            }

            if ($hasilInterview === 'tidak_lolos') {
                $stageCounts['interview_tidak_lolos']++;

                continue;
            }

            if ($hasilInterview === 'dipertimbangkan') {
                $stageCounts['interview_dipertimbangkan']++;

                continue;
            }

            if ($statusInterview === 'reschedule') {
                $stageCounts['interview_reschedule']++;

                continue;
            }

            if ($interviewRow) {
                $stageCounts['jadwal_interview']++;

                continue;
            }

            $hasilMmpiPelamar = $this->normalizeHasilTest($hasilMmpi->get($pelamarId));

            if ($hasilMmpiPelamar === 'lolos') {
                $stageCounts['hasil_test_mmpi_lolos']++;

                continue;
            }

            if ($hasilMmpiPelamar === 'gagal') {
                $stageCounts['hasil_test_mmpi_gagal']++;

                continue;
            }

            if ($jadwalMmpiIds->contains($pelamarId)) {
                $stageCounts['jadwal_test_mmpi']++;

                continue;
            }

            $hasilZoomPelamar = $this->normalizeHasilTest($hasilZoom->get($pelamarId));

            if ($hasilZoomPelamar === 'lolos') {
                $stageCounts['hasil_test_zoom_lolos']++;

                continue;
            }

            if ($hasilZoomPelamar === 'gagal') {
                $stageCounts['hasil_test_zoom_gagal']++;

                continue;
            }

            if ($jadwalZoomIds->contains($pelamarId)) {
                $stageCounts['jadwal_test_zoom']++;

                continue;
            }

            $stageCounts['administrasi']++;
        }

        $stageLabels = [
            'administrasi' => 'Administrasi',
            'jadwal_test_zoom' => 'Jadwal Test Zoom',
            'hasil_test_zoom_lolos' => 'Lolos Test Zoom',
            'hasil_test_zoom_gagal' => 'Tidak Lolos Test Zoom',
            'jadwal_test_mmpi' => 'Jadwal Test MMPI',
            'hasil_test_mmpi_lolos' => 'Lolos Test MMPI',
            'hasil_test_mmpi_gagal' => 'Tidak Lolos Test MMPI',
            'jadwal_interview' => 'Jadwal Interview',
            'interview_reschedule' => 'Interview Reschedule',
            'interview_lolos' => 'Lolos Interview',
            'interview_tidak_lolos' => 'Tidak Lolos Interview',
            'interview_dipertimbangkan' => 'Dipertimbangkan',
        ];

        $stages = collect($stageCounts)
            ->map(function ($count, $key) use ($stageLabels, $totalPelamar) {
                return [
                    'key' => $key,
                    'label' => $stageLabels[$key] ?? $key,
                    'total' => $count,
                    'percentage' => $totalPelamar > 0 ? round(($count / $totalPelamar) * 100, 1) : 0,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Data dashboard berhasil diambil.',
            'data' => [
                'total_pelamar' => $totalPelamar,
                'total_jadwal_test_zoom' => $jadwalZoomIds->count(),
                'total_jadwal_test_mmpi' => $jadwalMmpiIds->count(),
                'total_jadwal_interview' => $interview->count(),
                'stages' => $stages,
                'stage_counts' => $stageCounts,
                'monthly_applicants' => $this->getMonthlyApplicants(),
                'recent_pelamar' => $this->getRecentPelamar(),
                'insights' => $this->getOperationalInsights($pelamarIds, $stageCounts, $totalPelamar),
                'company_distribution' => $this->getCompanyDistribution(),
                'funnel' => $this->buildFunnel(
                    $totalPelamar,
                    $jadwalZoomIds->count(),
                    $jadwalMmpiIds->count(),
                    $interview->count(),
                    $stageCounts
                ),
                'filter_options' => $this->getFilterOptions(),
                'active_filters' => $this->dashboardFilters,
            ],
        ]);
    }

    public function aiInsights(
        Request $request,
        NineRouterService $nineRouter,
        RecruitmentAuditService $audit
    ): JsonResponse {
        $summaryResponse = $this->summary($request);
        $summary = $summaryResponse->getData(true)['data'] ?? [];

        // Hanya metrik agregat yang dikirim ke AI; recent_pelamar dan data pribadi tidak disertakan.
        $aggregateData = collect($summary)->only([
            'total_pelamar',
            'total_jadwal_test_zoom',
            'total_jadwal_test_mmpi',
            'total_jadwal_interview',
            'stages',
            'stage_counts',
            'monthly_applicants',
            'insights',
            'company_distribution',
            'funnel',
            'active_filters',
        ])->all();

        try {
            $result = $nineRouter->analyze($aggregateData, 'dashboard_insight');
        } catch (RuntimeException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 503);
        }

        $audit->record(
            self::class,
            (string) Str::uuid(),
            'dashboard_ai_analyzed',
            [
                'task' => 'dashboard_insight',
                'model' => $result['model'],
                'filters' => $summary['active_filters'] ?? [],
                'aggregate_only' => true,
            ],
            ($summary['active_filters']['company_id'] ?? null) ?: null
        );

        return response()->json([
            'success' => true,
            'message' => 'Insight AI dashboard berhasil dibuat.',
            'data' => $result,
        ]);
    }

    private function getPelamarIds(): Collection
    {
        if (! Schema::hasTable('data_riwayat_diri')) {
            return collect();
        }

        if (! Schema::hasColumn('data_riwayat_diri', 'id')) {
            return collect();
        }

        $query = DB::table('data_riwayat_diri');
        app(CompanyAccessService::class)->apply($query, Auth::user(), 'perusahaan_dilamar');

        $this->applyDashboardFilters($query);

        return $query
            ->whereNotNull('id')
            ->when(Schema::hasColumn('data_riwayat_diri', 'deleted_at'), fn ($q) => $q->whereNull('deleted_at'))
            ->pluck('id')
            ->unique()
            ->values();
    }

    private function getIdsFromTable(string $table, string $column = 'data_riwayat_diri_id'): Collection
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return collect();
        }

        $query = DB::table($table);
        $pelamarIds = $this->getPelamarIds();

        if ($pelamarIds->isEmpty()) {
            return collect();
        }

        $query->whereIn($column, $pelamarIds);

        if (Schema::hasColumn($table, 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        return $query
            ->whereNotNull($column)
            ->pluck($column)
            ->unique()
            ->values();
    }

    private function getHasilByPelamar(string $table): Collection
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'data_riwayat_diri_id')) {
            return collect();
        }

        $hasilColumn = $this->firstExistingColumn($table, [
            'hasil_test',
            'status_hasil',
            'hasil',
            'status',
        ]);

        if (! $hasilColumn) {
            return collect();
        }

        $query = DB::table($table)
            ->select('data_riwayat_diri_id', $hasilColumn)
            ->whereIn('data_riwayat_diri_id', $this->getPelamarIds());

        if (Schema::hasColumn($table, 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        if (Schema::hasColumn($table, 'updated_at')) {
            $query->orderByDesc('updated_at');
        }

        if (Schema::hasColumn($table, 'created_at')) {
            $query->orderByDesc('created_at');
        }

        return $query
            ->get()
            ->groupBy('data_riwayat_diri_id')
            ->map(function ($items) use ($hasilColumn) {
                return $items->first()->{$hasilColumn} ?? null;
            });
    }

    private function getInterviewByPelamar(): Collection
    {
        if (! Schema::hasTable('jadwal_interview_kandidat')) {
            return collect();
        }

        if (! Schema::hasColumn('jadwal_interview_kandidat', 'data_riwayat_diri_id')) {
            return collect();
        }

        $select = ['data_riwayat_diri_id'];

        foreach (['jadwal_interview_id', 'status_kehadiran', 'hasil_interview', 'catatan', 'created_at', 'updated_at'] as $column) {
            if (Schema::hasColumn('jadwal_interview_kandidat', $column)) {
                $select[] = $column;
            }
        }

        $query = DB::table('jadwal_interview_kandidat')
            ->select($select)
            ->whereIn('data_riwayat_diri_id', $this->getPelamarIds());

        if (Schema::hasColumn('jadwal_interview_kandidat', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        if (Schema::hasColumn('jadwal_interview_kandidat', 'updated_at')) {
            $query->orderByDesc('updated_at');
        }

        if (Schema::hasColumn('jadwal_interview_kandidat', 'created_at')) {
            $query->orderByDesc('created_at');
        }

        return $query
            ->get()
            ->groupBy('data_riwayat_diri_id')
            ->map(function ($items) {
                return $items->first();
            });
    }

    private function getMonthlyApplicants(int $months = 12): array
    {
        if (
            ! Schema::hasTable('data_riwayat_diri') ||
            ! Schema::hasColumn('data_riwayat_diri', 'created_at')
        ) {
            return [];
        }

        $startDate = now()->startOfMonth()->subMonths($months - 1);

        $driver = DB::connection()->getDriverName();

        $monthExpression = match ($driver) {
            'pgsql' => "TO_CHAR(created_at, 'YYYY-MM')",
            'sqlite' => "strftime('%Y-%m', created_at)",
            default => "DATE_FORMAT(created_at, '%Y-%m')",
        };

        $rows = DB::table('data_riwayat_diri')
            ->selectRaw("{$monthExpression} as month_key")
            ->selectRaw('COUNT(*) as total')
            ->whereNotNull('created_at')
            ->where('created_at', '>=', $startDate)
            ->whereIn('id', $this->getPelamarIds())
            ->groupByRaw($monthExpression)
            ->orderBy('month_key')
            ->get()
            ->keyBy('month_key');

        return collect(range(0, $months - 1))
            ->map(function ($index) use ($startDate, $rows) {
                $date = (clone $startDate)->addMonths($index);
                $monthKey = $date->format('Y-m');

                return [
                    'month' => $monthKey,
                    'label' => $date->format('M Y'),
                    'total' => (int) ($rows->get($monthKey)->total ?? 0),
                ];
            })
            ->values()
            ->all();
    }

    private function getRecentPelamar(): array
    {
        if (! Schema::hasTable('data_riwayat_diri')) {
            return [];
        }

        if (! Schema::hasColumn('data_riwayat_diri', 'id')) {
            return [];
        }

        $select = [
            'id',
        ];

        foreach ([
            'nama_lengkap',
            'nama_panggil',
            'email',
            'no_wa',
            'posisi_yang_dilamar',
            'perusahaan_dilamar',
            'created_at',
        ] as $column) {
            if (Schema::hasColumn('data_riwayat_diri', $column)) {
                $select[] = $column;
            }
        }

        $query = DB::table('data_riwayat_diri')->select($select);
        app(CompanyAccessService::class)->apply($query, Auth::user(), 'perusahaan_dilamar');
        $query->whereIn('id', $this->getPelamarIds());

        if (Schema::hasColumn('data_riwayat_diri', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        if (Schema::hasColumn('data_riwayat_diri', 'created_at')) {
            $query->orderByDesc('created_at');
        }

        return $query
            ->limit(8)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id ?? null,
                    'nama_lengkap' => $item->nama_lengkap ?? $item->nama_panggil ?? '-',
                    'nama_panggil' => $item->nama_panggil ?? null,
                    'email' => $item->email ?? null,
                    'no_wa' => $item->no_wa ?? null,
                    'posisi_yang_dilamar' => $this->getNamaPosisi($item->posisi_yang_dilamar ?? null),
                    'perusahaan_dilamar' => $this->getNamaPerusahaan($item->perusahaan_dilamar ?? null),
                    'created_at' => $item->created_at ?? null,
                ];
            })
            ->values()
            ->all();
    }

    private function firstExistingColumn(string $table, array $columns): ?string
    {
        foreach ($columns as $column) {
            if (Schema::hasColumn($table, $column)) {
                return $column;
            }
        }

        return null;
    }

    private function getOperationalInsights(Collection $pelamarIds, array $stages, int $total): array
    {
        $accepted = (int) ($stages['interview_lolos'] ?? 0);
        $failedIntegrations = 0;
        $staleIntegrations = 0;
        $auditsToday = 0;
        $downloadsToday = 0;

        if (Schema::hasTable('integration_deliveries')) {
            $deliveryQuery = DB::table('integration_deliveries');
            app(CompanyAccessService::class)->apply($deliveryQuery, Auth::user(), 'company_id');
            $failedIntegrations = (clone $deliveryQuery)->where('status', 'failed')->count();
            $staleIntegrations = (clone $deliveryQuery)
                ->whereIn('status', ['queued', 'processing'])
                ->where('updated_at', '<', now()->subMinutes(15))
                ->count();
        }

        if (Schema::hasTable('recruitment_audits')) {
            $auditQuery = DB::table('recruitment_audits');
            if (Schema::hasColumn('recruitment_audits', 'company_id')) {
                app(CompanyAccessService::class)->apply($auditQuery, Auth::user(), 'company_id');
            }
            $auditsToday = (clone $auditQuery)->where('created_at', '>=', now()->startOfDay())->count();
            $downloadsToday = (clone $auditQuery)
                ->where('event', 'downloaded')
                ->where('created_at', '>=', now()->startOfDay())
                ->count();
        }

        $offeringPending = 0;
        if (Schema::hasTable('jadwal_offering_letters') && Schema::hasTable('hasil_review_management')) {
            $offeringPending = DB::table('jadwal_offering_letters as jol')
                ->join('hasil_review_management as hrm', 'hrm.id', '=', 'jol.hasil_review_management_id')
                ->join('jadwal_interview_kandidat as jik', 'jik.id', '=', 'hrm.hasil_interview_id')
                ->whereIn('jik.data_riwayat_diri_id', $pelamarIds)
                ->whereNull('jol.status_jadwal')
                ->count();
        }

        $attention = collect([
            ['key' => 'failed_integrations', 'label' => 'Integrasi gagal', 'total' => $failedIntegrations, 'severity' => 'critical', 'menu' => null],
            ['key' => 'stale_integrations', 'label' => 'Antrean tertunda >15 menit', 'total' => $staleIntegrations, 'severity' => 'warning', 'menu' => null],
            ['key' => 'interview_reschedule', 'label' => 'Interview perlu dijadwalkan ulang', 'total' => (int) ($stages['interview_reschedule'] ?? 0), 'severity' => 'warning', 'menu' => 'interview-kandidat'],
            ['key' => 'offering_pending', 'label' => 'Offering Letter menunggu keputusan', 'total' => $offeringPending, 'severity' => 'info', 'menu' => 'jadwal-ol'],
        ])->filter(fn ($item) => $item['total'] > 0)->values()->all();

        return [
            'conversion_rate' => $total > 0 ? round(($accepted / $total) * 100, 1) : 0,
            'accepted_candidates' => $accepted,
            'failed_integrations' => $failedIntegrations,
            'stale_integrations' => $staleIntegrations,
            'offering_pending' => $offeringPending,
            'audits_today' => $auditsToday,
            'downloads_today' => $downloadsToday,
            'attention_items' => $attention,
            'health' => ($failedIntegrations + $staleIntegrations) > 0 ? 'attention' : 'healthy',
        ];
    }

    private function getCompanyDistribution(): array
    {
        if (! Schema::hasTable('data_riwayat_diri')) {
            return [];
        }

        $query = DB::table('data_riwayat_diri as drd')
            ->leftJoin('data_perusahaan as dp', 'dp.id', '=', 'drd.perusahaan_dilamar')
            ->selectRaw('drd.perusahaan_dilamar as company_id')
            ->selectRaw("COALESCE(dp.nama_perusahaan, '-') as company_name")
            ->selectRaw('COUNT(*) as total')
            ->whereNull('drd.deleted_at')
            ->groupBy('drd.perusahaan_dilamar', 'dp.nama_perusahaan')
            ->orderByDesc('total')
            ->limit(8);

        app(CompanyAccessService::class)->apply($query, Auth::user(), 'drd.perusahaan_dilamar');
        $query->whereIn('drd.id', $this->getPelamarIds());

        return $query->get()->map(fn ($row) => [
            'id' => $row->company_id,
            'name' => $row->company_name,
            'total' => (int) $row->total,
        ])->all();
    }

    private function applyDashboardFilters($query): void
    {
        if (! Schema::hasTable('data_riwayat_diri')) {
            return;
        }

        $filters = $this->dashboardFilters;

        if (! empty($filters['date_from']) && Schema::hasColumn('data_riwayat_diri', 'created_at')) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to']) && Schema::hasColumn('data_riwayat_diri', 'created_at')) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (! empty($filters['company_id']) && Schema::hasColumn('data_riwayat_diri', 'perusahaan_dilamar')) {
            $query->where('perusahaan_dilamar', $filters['company_id']);
        }

        if (! empty($filters['position_id']) && Schema::hasColumn('data_riwayat_diri', 'posisi_yang_dilamar')) {
            $query->where('posisi_yang_dilamar', $filters['position_id']);
        }

        if (! empty($filters['source_id']) && Schema::hasColumn('data_riwayat_diri', 'sumber_informasi_id')) {
            $query->where('sumber_informasi_id', $filters['source_id']);
        }
    }

    private function buildFunnel(int $total, int $zoom, int $mmpi, int $interview, array $stages): array
    {
        $finalDecision = (int) ($stages['interview_lolos'] ?? 0)
            + (int) ($stages['interview_tidak_lolos'] ?? 0)
            + (int) ($stages['interview_dipertimbangkan'] ?? 0);
        $accepted = (int) ($stages['interview_lolos'] ?? 0);

        $steps = [
            ['key' => 'applicants', 'label' => 'Pelamar Masuk', 'total' => $total],
            ['key' => 'zoom', 'label' => 'Masuk Test Zoom', 'total' => $zoom],
            ['key' => 'mmpi', 'label' => 'Masuk Test MMPI', 'total' => $mmpi],
            ['key' => 'interview', 'label' => 'Masuk Interview', 'total' => $interview],
            ['key' => 'decision', 'label' => 'Keputusan Interview', 'total' => $finalDecision],
            ['key' => 'accepted', 'label' => 'Lolos Interview', 'total' => $accepted],
        ];

        $previous = null;
        foreach ($steps as &$step) {
            $step['overall_rate'] = $total > 0 ? round(($step['total'] / $total) * 100, 1) : 0;
            $step['step_rate'] = $previous !== null && $previous > 0
                ? round(($step['total'] / $previous) * 100, 1)
                : ($step['key'] === 'applicants' && $total > 0 ? 100 : 0);
            $step['drop_off'] = $previous !== null ? max($previous - $step['total'], 0) : 0;
            $step['drop_off_rate'] = $previous !== null && $previous > 0
                ? round((max($previous - $step['total'], 0) / $previous) * 100, 1)
                : 0;
            $previous = $step['total'];
        }
        unset($step);

        $bottleneck = collect($steps)->skip(1)->sortByDesc('drop_off_rate')->first();

        return [
            'steps' => $steps,
            'bottleneck' => $bottleneck,
        ];
    }

    private function getFilterOptions(): array
    {
        if (! Schema::hasTable('data_riwayat_diri')) {
            return ['companies' => [], 'positions' => [], 'sources' => []];
        }

        $query = DB::table('data_riwayat_diri');
        app(CompanyAccessService::class)->apply($query, Auth::user(), 'perusahaan_dilamar');
        $accessibleIds = $query->pluck('id');

        $companies = Schema::hasTable('data_perusahaan')
            ? DB::table('data_perusahaan as dp')
                ->join('data_riwayat_diri as drd', 'drd.perusahaan_dilamar', '=', 'dp.id')
                ->whereIn('drd.id', $accessibleIds)
                ->select('dp.id', 'dp.nama_perusahaan as label')
                ->distinct()->orderBy('label')->get()->all()
            : [];

        $positions = Schema::hasTable('posisi')
            ? DB::table('posisi as p')
                ->join('data_riwayat_diri as drd', 'drd.posisi_yang_dilamar', '=', 'p.id')
                ->whereIn('drd.id', $accessibleIds)
                ->select('p.id', 'p.nama_posisi as label')
                ->distinct()->orderBy('label')->get()->all()
            : [];

        $sources = Schema::hasTable('sumber_informasi') && Schema::hasColumn('data_riwayat_diri', 'sumber_informasi_id')
            ? DB::table('sumber_informasi as si')
                ->join('data_riwayat_diri as drd', 'drd.sumber_informasi_id', '=', 'si.id')
                ->whereIn('drd.id', $accessibleIds)
                ->select('si.id', 'si.informasi as label')
                ->distinct()->orderBy('label')->get()->all()
            : [];

        return ['companies' => $companies, 'positions' => $positions, 'sources' => $sources];
    }

    private function normalizeHasilTest($value): ?string
    {
        $normalized = strtolower(trim((string) $value));

        if ($normalized === '') {
            return null;
        }

        if (in_array($normalized, ['lolos', 'passed', 'pass'], true)) {
            return 'lolos';
        }

        if (in_array($normalized, ['tidak_lolos', 'tidak lolos', 'gagal', 'failed', 'fail'], true)) {
            return 'gagal';
        }

        return $normalized;
    }

    private function normalizeHasilInterview($value): ?string
    {
        $normalized = strtolower(trim((string) $value));

        if ($normalized === '') {
            return null;
        }

        if (in_array($normalized, ['lolos', 'lolos interview'], true)) {
            return 'lolos';
        }

        if (in_array($normalized, ['tidak_lolos', 'tidak lolos', 'tidak lolos interview', 'gagal'], true)) {
            return 'tidak_lolos';
        }

        if (in_array($normalized, ['dipertimbangkan', 'dipertimbangkan interview', 'pertimbangan'], true)) {
            return 'dipertimbangkan';
        }

        return $normalized;
    }

    private function normalizeStatusKehadiran($value): ?string
    {
        $normalized = strtolower(trim((string) $value));

        if ($normalized === '') {
            return null;
        }

        if (in_array($normalized, ['hadir', 'tidak hadir', 'tidak_hadir', 'tidak respon', 'tidak_respon'], true)) {
            return str_replace(' ', '_', $normalized);
        }

        if (in_array($normalized, ['reschedule', 'rescheduled', 'jadwal ulang', 'jadwal_ulang'], true)) {
            return 'reschedule';
        }

        return $normalized;
    }

    private function getNamaPosisi($posisiId): string
    {
        if (empty($posisiId)) {
            return '-';
        }

        if (! Schema::hasTable('posisi')) {
            return (string) $posisiId;
        }

        if (! Schema::hasColumn('posisi', 'id')) {
            return (string) $posisiId;
        }

        $posisi = DB::table('posisi')
            ->where('id', $posisiId)
            ->first();

        if (! $posisi) {
            return (string) $posisiId;
        }

        foreach (['nama_posisi', 'posisi', 'nama', 'nama_jabatan', 'jabatan'] as $column) {
            if (isset($posisi->{$column}) && ! empty($posisi->{$column})) {
                return $posisi->{$column};
            }
        }

        return (string) $posisiId;
    }

    private function getNamaPerusahaan($perusahaanId): string
    {
        if (empty($perusahaanId)) {
            return '-';
        }

        $table = null;

        if (Schema::hasTable('data_perusahaan')) {
            $table = 'data_perusahaan';
        } elseif (Schema::hasTable('perusahaan')) {
            $table = 'perusahaan';
        }

        if (! $table) {
            return (string) $perusahaanId;
        }

        if (! Schema::hasColumn($table, 'id')) {
            return (string) $perusahaanId;
        }

        $perusahaan = DB::table($table)
            ->where('id', $perusahaanId)
            ->first();

        if (! $perusahaan) {
            return (string) $perusahaanId;
        }

        foreach (['nama_perusahaan', 'perusahaan', 'nama'] as $column) {
            if (isset($perusahaan->{$column}) && ! empty($perusahaan->{$column})) {
                return $perusahaan->{$column};
            }
        }

        return (string) $perusahaanId;
    }
}
