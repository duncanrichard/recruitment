<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Services\CompanyAccessService;
use App\Services\SpreadsheetValueSanitizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportInterviewKandidatController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tanggal_awal' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_awal'],
            'status_kehadiran' => ['nullable', 'in:semua,hadir,tidak_hadir,tidak_respon,reschedule,belum_ada'],
            'hasil_interview' => ['nullable', 'in:semua,lolos_interview,tidak_lolos_interview,dipertimbangkan,belum_ada'],
        ], [
            'tanggal_awal.date' => 'Tanggal awal tidak valid.',
            'tanggal_akhir.date' => 'Tanggal akhir tidak valid.',
            'tanggal_akhir.after_or_equal' => 'Tanggal akhir tidak boleh lebih kecil dari tanggal awal.',
        ]);

        $query = $this->baseQuery($validated);
        $summaryItems = (clone $query)->get();

        $summary = [
            'total' => $summaryItems->count(),

            'hadir' => $summaryItems->filter(fn ($item) => $this->normalizeKehadiran($item->status_kehadiran) === 'hadir')->count(),
            'tidak_hadir' => $summaryItems->filter(fn ($item) => $this->normalizeKehadiran($item->status_kehadiran) === 'tidak_hadir')->count(),
            'tidak_respon' => $summaryItems->filter(fn ($item) => $this->normalizeKehadiran($item->status_kehadiran) === 'tidak_respon')->count(),
            'reschedule' => $summaryItems->filter(fn ($item) => $this->normalizeKehadiran($item->status_kehadiran) === 'reschedule')->count(),
            'belum_kehadiran' => $summaryItems->filter(fn ($item) => $this->normalizeKehadiran($item->status_kehadiran) === null)->count(),

            'lolos_interview' => $summaryItems->filter(fn ($item) => $this->normalizeHasilInterview($item->hasil_interview) === 'lolos_interview')->count(),
            'tidak_lolos_interview' => $summaryItems->filter(fn ($item) => $this->normalizeHasilInterview($item->hasil_interview) === 'tidak_lolos_interview')->count(),
            'dipertimbangkan' => $summaryItems->filter(fn ($item) => $this->normalizeHasilInterview($item->hasil_interview) === 'dipertimbangkan')->count(),
            'belum_hasil' => $summaryItems->filter(fn ($item) => $this->normalizeHasilInterview($item->hasil_interview) === null)->count(),
        ];

        $data = $query
            ->orderByDesc('ji.jadwal_interview')
            ->orderBy('drd.nama_lengkap')
            ->paginate(25);

        $data->getCollection()->transform(fn ($item) => $this->formatRow($item));

        return response()->json([
            'success' => true,
            'message' => 'Report interview kandidat berhasil ditampilkan.',
            'summary' => $summary,
            'data' => $data,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'tanggal_awal' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_awal'],
            'status_kehadiran' => ['nullable', 'in:semua,hadir,tidak_hadir,tidak_respon,reschedule,belum_ada'],
            'hasil_interview' => ['nullable', 'in:semua,lolos_interview,tidak_lolos_interview,dipertimbangkan,belum_ada'],
        ], [
            'tanggal_awal.date' => 'Tanggal awal tidak valid.',
            'tanggal_akhir.date' => 'Tanggal akhir tidak valid.',
            'tanggal_akhir.after_or_equal' => 'Tanggal akhir tidak boleh lebih kecil dari tanggal awal.',
        ]);

        $rows = $this->baseQuery($validated)
            ->orderByDesc('ji.jadwal_interview')
            ->orderBy('drd.nama_lengkap')
            ->get()
            ->map(fn ($item) => $this->formatRow($item));

        $tanggalAwal = $validated['tanggal_awal'] ?? 'Semua';
        $tanggalAkhir = $validated['tanggal_akhir'] ?? 'Semua';
        $rows = app(SpreadsheetValueSanitizer::class)->sanitizeRows($rows);
        $filename = 'report-interview-kandidat-'.now()->format('Ymd-His').'.xls';

        return response()->streamDownload(function () use ($rows, $tanggalAwal, $tanggalAkhir) {
            echo '<html>';
            echo '<head>';
            echo '<meta charset="UTF-8">';
            echo '<style>
                body { font-family: Arial, sans-serif; }
                table { border-collapse: collapse; width: 100%; }
                th, td {
                    border: 1px solid #999;
                    padding: 6px;
                    font-size: 12px;
                    vertical-align: top;
                    mso-number-format: "\\@";
                }
                th {
                    background: #e5e7eb;
                    font-weight: bold;
                    text-align: center;
                }
                .title {
                    font-size: 18px;
                    font-weight: bold;
                    margin-bottom: 8px;
                }
                .subtitle {
                    font-size: 12px;
                    margin-bottom: 16px;
                }
            </style>';
            echo '</head>';
            echo '<body>';

            echo '<div class="title">Report Interview Kandidat</div>';
            echo '<div class="subtitle">Tanggal Interview: '.e($tanggalAwal).' s/d '.e($tanggalAkhir).'</div>';

            echo '<table>';
            echo '<thead>';
            echo '<tr>';
            echo '<th>No</th>';
            echo '<th>Judul Interview</th>';
            echo '<th>Tanggal Interview</th>';
            echo '<th>Interview Oleh</th>';
            echo '<th>Nama Kandidat</th>';
            echo '<th>Nama Panggil</th>';
            echo '<th>Email</th>';
            echo '<th>No WA</th>';
            echo '<th>Posisi Dilamar</th>';
            echo '<th>Status Kehadiran</th>';
            echo '<th>Hasil Interview</th>';
            echo '<th>Catatan</th>';
            echo '<th>Created At</th>';
            echo '<th>Updated At</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            if ($rows->count() === 0) {
                echo '<tr>';
                echo '<td colspan="14" style="text-align:center;">Data tidak ditemukan</td>';
                echo '</tr>';
            }

            foreach ($rows as $index => $row) {
                echo '<tr>';
                echo '<td>'.($index + 1).'</td>';
                echo '<td>'.e($row['judul_interview']).'</td>';
                echo '<td>'.e($row['jadwal_interview']).'</td>';
                echo '<td>'.e($row['interviewer']).'</td>';
                echo '<td>'.e($row['nama_lengkap']).'</td>';
                echo '<td>'.e($row['nama_panggil']).'</td>';
                echo '<td>'.e($row['email']).'</td>';
                echo '<td>'.e($row['no_wa']).'</td>';
                echo '<td>'.e($row['posisi_dilamar']).'</td>';
                echo '<td>'.e($row['status_kehadiran_label']).'</td>';
                echo '<td>'.e($row['hasil_interview_label']).'</td>';
                echo '<td>'.e($row['catatan']).'</td>';
                echo '<td>'.e($row['created_at']).'</td>';
                echo '<td>'.e($row['updated_at']).'</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
            echo '</body>';
            echo '</html>';
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    private function baseQuery(array $filters)
    {
        $interviewerExpression = $this->interviewerExpression();
        $posisiExpression = $this->posisiExpression();

        $query = DB::table('jadwal_interview_kandidat as jik')
            ->join('jadwal_interview as ji', 'ji.id', '=', 'jik.jadwal_interview_id')
            ->join('data_riwayat_diri as drd', 'drd.id', '=', 'jik.data_riwayat_diri_id');

        app(CompanyAccessService::class)->apply($query, Auth::user(), 'drd.perusahaan_dilamar');

        if (Schema::hasTable('posisi') && Schema::hasColumn('data_riwayat_diri', 'posisi_yang_dilamar')) {
            $query->leftJoin('posisi as p', 'p.id', '=', 'drd.posisi_yang_dilamar');
        }

        if (Schema::hasColumn('jadwal_interview_kandidat', 'deleted_at')) {
            $query->whereNull('jik.deleted_at');
        }

        if (Schema::hasColumn('jadwal_interview', 'deleted_at')) {
            $query->whereNull('ji.deleted_at');
        }

        if (Schema::hasColumn('data_riwayat_diri', 'deleted_at')) {
            $query->whereNull('drd.deleted_at');
        }

        $query->select([
            'jik.id',
            'jik.jadwal_interview_id',
            'jik.data_riwayat_diri_id',
            'jik.status_kehadiran',
            'jik.hasil_interview',
            'jik.catatan',
            'jik.created_at',
            'jik.updated_at',

            'ji.judul_interview',
            'ji.jadwal_interview',

            'drd.nama_lengkap',
            'drd.nama_panggil',
            'drd.email',
            'drd.no_wa',

            DB::raw($posisiExpression.' as posisi_dilamar'),
            DB::raw($interviewerExpression.' as interviewer'),
        ]);

        if (! empty($filters['tanggal_awal'])) {
            $query->whereDate(
                'ji.jadwal_interview',
                '>=',
                Carbon::parse($filters['tanggal_awal'])->toDateString()
            );
        }

        if (! empty($filters['tanggal_akhir'])) {
            $query->whereDate(
                'ji.jadwal_interview',
                '<=',
                Carbon::parse($filters['tanggal_akhir'])->toDateString()
            );
        }

        $statusKehadiran = $filters['status_kehadiran'] ?? 'semua';

        if ($statusKehadiran === 'hadir') {
            $query->whereRaw("LOWER(TRIM(COALESCE(jik.status_kehadiran, ''))) = 'hadir'");
        }

        if ($statusKehadiran === 'tidak_hadir') {
            $query->whereRaw("LOWER(REPLACE(TRIM(COALESCE(jik.status_kehadiran, '')), ' ', '_')) = 'tidak_hadir'");
        }

        if ($statusKehadiran === 'tidak_respon') {
            $query->whereRaw("LOWER(REPLACE(TRIM(COALESCE(jik.status_kehadiran, '')), ' ', '_')) = 'tidak_respon'");
        }

        if ($statusKehadiran === 'reschedule') {
            $query->whereRaw("LOWER(TRIM(COALESCE(jik.status_kehadiran, ''))) = 'reschedule'");
        }

        if ($statusKehadiran === 'belum_ada') {
            $query->where(function ($q) {
                $q->whereNull('jik.status_kehadiran')
                    ->orWhereRaw("TRIM(COALESCE(jik.status_kehadiran, '')) = ''");
            });
        }

        $hasilInterview = $filters['hasil_interview'] ?? 'semua';

        if ($hasilInterview === 'lolos_interview') {
            $query->whereRaw("LOWER(TRIM(COALESCE(jik.hasil_interview, ''))) = 'lolos interview'");
        }

        if ($hasilInterview === 'tidak_lolos_interview') {
            $query->whereRaw("LOWER(TRIM(COALESCE(jik.hasil_interview, ''))) = 'tidak lolos interview'");
        }

        if ($hasilInterview === 'dipertimbangkan') {
            $query->whereRaw("LOWER(TRIM(COALESCE(jik.hasil_interview, ''))) = 'dipertimbangkan'");
        }

        if ($hasilInterview === 'belum_ada') {
            $query->where(function ($q) {
                $q->whereNull('jik.hasil_interview')
                    ->orWhereRaw("TRIM(COALESCE(jik.hasil_interview, '')) = ''");
            });
        }

        return $query;
    }

    private function interviewerExpression(): string
    {
        /*
        |--------------------------------------------------------------------------
        | Prioritas sumber interviewer:
        | 1. Kolom langsung di jadwal_interview: panelis/interviewer/nama_interviewer/dll
        | 2. Kolom FK di jadwal_interview: interviewer_id/panelis_id
        | 3. Pivot table jika ada: jadwal_interview_interviewer / jadwal_interview_panelis
        |--------------------------------------------------------------------------
        */

        $directColumn = $this->firstExistingColumn('jadwal_interview', [
            'panelis',
            'interviewer',
            'nama_interviewer',
            'interview_oleh',
            'diinterview_oleh',
        ]);

        if ($directColumn) {
            return "COALESCE(ji.{$directColumn}::text, '-')";
        }

        $fkColumn = $this->firstExistingColumn('jadwal_interview', [
            'interviewer_id',
            'panelis_id',
        ]);

        $interviewerTable = $this->firstExistingTable([
            'interviewer',
            'interviewers',
            'data_interviewer',
            'master_interviewer',
        ]);

        if ($fkColumn && $interviewerTable) {
            $nameColumn = $this->firstExistingColumn($interviewerTable, [
                'nama_interviewer',
                'nama_lengkap',
                'nama',
                'name',
                'interviewer',
                'panelis',
            ]);

            if ($nameColumn) {
                return "COALESCE((
                    SELECT iv.{$nameColumn}::text
                    FROM {$interviewerTable} iv
                    WHERE iv.id = ji.{$fkColumn}
                    LIMIT 1
                ), ji.{$fkColumn}::text, '-')";
            }

            return "COALESCE(ji.{$fkColumn}::text, '-')";
        }

        if ($fkColumn) {
            return "COALESCE(ji.{$fkColumn}::text, '-')";
        }

        $pivotTable = $this->firstExistingTable([
            'jadwal_interview_interviewer',
            'jadwal_interview_panelis',
            'jadwal_interviewer',
            'interview_jadwal_interviewer',
        ]);

        if ($pivotTable && $interviewerTable) {
            $pivotJadwalColumn = $this->firstExistingColumn($pivotTable, [
                'jadwal_interview_id',
                'interview_id',
            ]);

            $pivotInterviewerColumn = $this->firstExistingColumn($pivotTable, [
                'interviewer_id',
                'panelis_id',
            ]);

            $nameColumn = $this->firstExistingColumn($interviewerTable, [
                'nama_interviewer',
                'nama_lengkap',
                'nama',
                'name',
                'interviewer',
                'panelis',
            ]);

            if ($pivotJadwalColumn && $pivotInterviewerColumn && $nameColumn) {
                return "COALESCE((
                    SELECT STRING_AGG(iv.{$nameColumn}::text, ', ')
                    FROM {$pivotTable} piv
                    JOIN {$interviewerTable} iv ON iv.id = piv.{$pivotInterviewerColumn}
                    WHERE piv.{$pivotJadwalColumn} = ji.id
                ), '-')";
            }
        }

        return "'-'";
    }

    private function posisiExpression(): string
    {
        if (Schema::hasTable('posisi')) {
            $posisiColumn = $this->firstExistingColumn('posisi', [
                'nama_posisi',
                'posisi',
                'nama',
                'nama_jabatan',
                'jabatan',
            ]);

            if ($posisiColumn && Schema::hasColumn('data_riwayat_diri', 'posisi_yang_dilamar')) {
                return "COALESCE(p.{$posisiColumn}::text, drd.posisi_yang_dilamar::text, '-')";
            }
        }

        if (Schema::hasColumn('data_riwayat_diri', 'posisi_yang_dilamar')) {
            return "COALESCE(drd.posisi_yang_dilamar::text, '-')";
        }

        return "'-'";
    }

    private function formatRow($item): array
    {
        $statusKehadiran = $this->normalizeKehadiran($item->status_kehadiran ?? null);
        $hasilInterview = $this->normalizeHasilInterview($item->hasil_interview ?? null);

        return [
            'id' => $item->id,
            'jadwal_interview_id' => $item->jadwal_interview_id,
            'data_riwayat_diri_id' => $item->data_riwayat_diri_id,

            'judul_interview' => $item->judul_interview ?: '-',
            'jadwal_interview' => $item->jadwal_interview ? date('Y-m-d H:i:s', strtotime($item->jadwal_interview)) : null,

            'interviewer' => $item->interviewer ?: '-',
            'interview_oleh' => $item->interviewer ?: '-',
            'diinterview_oleh' => $item->interviewer ?: '-',

            'nama_lengkap' => $item->nama_lengkap ?: '-',
            'nama_panggil' => $item->nama_panggil ?: '-',
            'email' => $item->email ?: '-',
            'no_wa' => $item->no_wa ?: '-',
            'posisi_dilamar' => $item->posisi_dilamar ?: '-',

            'status_kehadiran' => $statusKehadiran,
            'status_kehadiran_label' => $this->labelKehadiran($statusKehadiran),

            'hasil_interview' => $hasilInterview,
            'hasil_interview_label' => $this->labelHasilInterview($hasilInterview),

            'catatan' => $item->catatan ?: '-',
            'created_at' => $item->created_at ? date('Y-m-d H:i:s', strtotime($item->created_at)) : null,
            'updated_at' => $item->updated_at ? date('Y-m-d H:i:s', strtotime($item->updated_at)) : null,
        ];
    }

    private function normalizeKehadiran($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = strtolower(trim((string) $value));
        $normalized = str_replace([' ', '-'], '_', $normalized);

        return match ($normalized) {
            'hadir' => 'hadir',
            'tidak_hadir', 'tidakhadir', 'tidak' => 'tidak_hadir',
            'tidak_respon', 'tidakrespon' => 'tidak_respon',
            'reschedule' => 'reschedule',
            default => null,
        };
    }

    private function normalizeHasilInterview($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = strtolower(trim((string) $value));
        $normalized = str_replace(['-', '_'], ' ', $normalized);
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        return match ($normalized) {
            'lolos interview', 'lolos', 'diterima' => 'lolos_interview',
            'tidak lolos interview', 'tidak lolos', 'gagal' => 'tidak_lolos_interview',
            'dipertimbangkan' => 'dipertimbangkan',
            default => null,
        };
    }

    private function labelKehadiran(?string $value): string
    {
        return match ($value) {
            'hadir' => 'Hadir',
            'tidak_hadir' => 'Tidak Hadir',
            'tidak_respon' => 'Tidak Respon',
            'reschedule' => 'Reschedule',
            default => 'Belum Ada',
        };
    }

    private function labelHasilInterview(?string $value): string
    {
        return match ($value) {
            'lolos_interview' => 'Lolos Interview',
            'tidak_lolos_interview' => 'Tidak Lolos Interview',
            'dipertimbangkan' => 'Dipertimbangkan',
            default => 'Belum Ada',
        };
    }

    private function firstExistingColumn(string $table, array $columns): ?string
    {
        if (! Schema::hasTable($table)) {
            return null;
        }

        foreach ($columns as $column) {
            if (Schema::hasColumn($table, $column)) {
                return $column;
            }
        }

        return null;
    }

    private function firstExistingTable(array $tables): ?string
    {
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                return $table;
            }
        }

        return null;
    }
}
