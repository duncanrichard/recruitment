<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        return view('pages.admin.index');
    }

    public function summary(): JsonResponse
    {
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
            ],
        ]);
    }

    private function getPelamarIds(): Collection
    {
        if (! Schema::hasTable('data_riwayat_diri')) {
            return collect();
        }

        if (! Schema::hasColumn('data_riwayat_diri', 'uuid')) {
            return collect();
        }

        return DB::table('data_riwayat_diri')
            ->whereNotNull('uuid')
            ->pluck('uuid')
            ->unique()
            ->values();
    }

    private function getIdsFromTable(string $table, string $column = 'data_riwayat_diri_id'): Collection
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return collect();
        }

        $query = DB::table($table);

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
            ->select('data_riwayat_diri_id', $hasilColumn);

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

        $query = DB::table('jadwal_interview_kandidat')->select($select);

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

        $monthExpression = $driver === 'pgsql'
            ? "TO_CHAR(created_at, 'YYYY-MM')"
            : "DATE_FORMAT(created_at, '%Y-%m')";

        $rows = DB::table('data_riwayat_diri')
            ->selectRaw("{$monthExpression} as month_key")
            ->selectRaw('COUNT(*) as total')
            ->whereNotNull('created_at')
            ->where('created_at', '>=', $startDate)
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

        if (! Schema::hasColumn('data_riwayat_diri', 'uuid')) {
            return [];
        }

        $select = [
            DB::raw('uuid as id'),
            'uuid',
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
                    'id' => $item->id ?? $item->uuid ?? null,
                    'uuid' => $item->uuid ?? $item->id ?? null,
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