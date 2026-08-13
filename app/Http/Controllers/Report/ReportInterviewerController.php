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
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportInterviewerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tanggal_awal' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_awal'],
            'interviewer' => ['nullable', 'string', 'max:255'],
        ], [
            'tanggal_awal.date' => 'Tanggal awal tidak valid.',
            'tanggal_akhir.date' => 'Tanggal akhir tidak valid.',
            'tanggal_akhir.after_or_equal' => 'Tanggal akhir tidak boleh lebih kecil dari tanggal awal.',
        ]);

        $rows = $this->baseQuery($validated)
            ->orderByDesc('total_kandidat')
            ->orderByDesc('total_jadwal')
            ->orderBy('i.nama')
            ->paginate(25);

        $rows->getCollection()->transform(fn ($item) => $this->formatRow($item));

        $dashboardRows = $this->baseQuery($validated)
            ->orderByDesc('total_kandidat')
            ->orderByDesc('total_jadwal')
            ->orderBy('i.nama')
            ->get()
            ->map(fn ($item) => $this->formatRow($item));

        return response()->json([
            'success' => true,
            'message' => 'Report interviewer berhasil ditampilkan.',
            'data' => [
                'rows' => $rows,
                'dashboard' => $this->buildDashboard($dashboardRows),
            ],
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'tanggal_awal' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_awal'],
            'interviewer' => ['nullable', 'string', 'max:255'],
        ], [
            'tanggal_awal.date' => 'Tanggal awal tidak valid.',
            'tanggal_akhir.date' => 'Tanggal akhir tidak valid.',
            'tanggal_akhir.after_or_equal' => 'Tanggal akhir tidak boleh lebih kecil dari tanggal awal.',
        ]);

        $rows = $this->baseQuery($validated)
            ->orderByDesc('total_kandidat')
            ->orderByDesc('total_jadwal')
            ->orderBy('i.nama')
            ->get()
            ->map(fn ($item) => $this->formatRow($item));

        $tanggalAwal = $validated['tanggal_awal'] ?? 'Semua';
        $tanggalAkhir = $validated['tanggal_akhir'] ?? 'Semua';
        $interviewer = $validated['interviewer'] ?? 'Semua';

        $rows = app(SpreadsheetValueSanitizer::class)->sanitizeRows($rows);
        $filename = 'report-interviewer-'.now()->format('Ymd-His').'.xls';

        return response()->streamDownload(function () use ($rows, $tanggalAwal, $tanggalAkhir, $interviewer) {
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

            echo '<div class="title">Report Interviewer</div>';
            echo '<div class="subtitle">';
            echo 'Tanggal Interview: '.e($tanggalAwal).' s/d '.e($tanggalAkhir);
            echo '<br>';
            echo 'Interviewer: '.e($interviewer);
            echo '</div>';

            echo '<table>';
            echo '<thead>';
            echo '<tr>';
            echo '<th>No</th>';
            echo '<th>Nama Interviewer</th>';
            echo '<th>No WA</th>';
            echo '<th>Total Jadwal</th>';
            echo '<th>Total Kandidat</th>';
            echo '<th>Kandidat Hadir</th>';
            echo '<th>Kandidat Tidak Hadir</th>';
            echo '<th>Tidak Respon</th>';
            echo '<th>Reschedule</th>';
            echo '<th>Lolos Interview</th>';
            echo '<th>Tidak Lolos Interview</th>';
            echo '<th>Dipertimbangkan</th>';
            echo '<th>Belum Hasil</th>';
            echo '<th>Interview Terakhir</th>';
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
                echo '<td>'.e($row['nama']).'</td>';
                echo '<td>'.e($row['no_wa']).'</td>';
                echo '<td>'.e($row['total_jadwal']).'</td>';
                echo '<td>'.e($row['total_kandidat']).'</td>';
                echo '<td>'.e($row['hadir']).'</td>';
                echo '<td>'.e($row['tidak_hadir']).'</td>';
                echo '<td>'.e($row['tidak_respon']).'</td>';
                echo '<td>'.e($row['reschedule']).'</td>';
                echo '<td>'.e($row['lolos_interview']).'</td>';
                echo '<td>'.e($row['tidak_lolos_interview']).'</td>';
                echo '<td>'.e($row['dipertimbangkan']).'</td>';
                echo '<td>'.e($row['belum_hasil']).'</td>';
                echo '<td>'.e($row['interview_terakhir']).'</td>';
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
        $query = DB::table('interviewers as i')
            ->leftJoin('jadwal_interview_panelis as jip', 'jip.interviewer_id', '=', 'i.id')
            ->leftJoin('jadwal_interview as ji', function ($join) {
                $join->on('ji.id', '=', 'jip.jadwal_interview_id');

                if ($this->hasColumn('jadwal_interview', 'deleted_at')) {
                    $join->whereNull('ji.deleted_at');
                }
            })
            ->leftJoin('jadwal_interview_kandidat as jik', function ($join) {
                $join->on('jik.jadwal_interview_id', '=', 'ji.id');

                if ($this->hasColumn('jadwal_interview_kandidat', 'deleted_at')) {
                    $join->whereNull('jik.deleted_at');
                }
            })
            ->leftJoin('data_riwayat_diri as drd', 'drd.id', '=', 'jik.data_riwayat_diri_id')
            ->whereNull('i.deleted_at');

        app(CompanyAccessService::class)->apply($query, Auth::user(), 'drd.perusahaan_dilamar');

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

        if (! empty($filters['interviewer'])) {
            $keyword = $filters['interviewer'];

            $query->where(function ($q) use ($keyword) {
                $q->where('i.nama', 'like', '%'.$keyword.'%')
                    ->orWhere('i.no_wa', 'like', '%'.$keyword.'%');
            });
        }

        return $query
            ->select([
                'i.id',
                'i.nama',
                'i.no_wa',
                DB::raw('COUNT(DISTINCT ji.id) as total_jadwal'),
                DB::raw('COUNT(DISTINCT jik.id) as total_kandidat'),

                DB::raw("
                    SUM(
                        CASE
                            WHEN LOWER(REPLACE(TRIM(COALESCE(jik.status_kehadiran, '')), ' ', '_')) = 'hadir'
                            THEN 1 ELSE 0
                        END
                    ) as hadir
                "),

                DB::raw("
                    SUM(
                        CASE
                            WHEN LOWER(REPLACE(TRIM(COALESCE(jik.status_kehadiran, '')), ' ', '_')) IN ('tidak_hadir', 'tidakhadir', 'tidak')
                            THEN 1 ELSE 0
                        END
                    ) as tidak_hadir
                "),

                DB::raw("
                    SUM(
                        CASE
                            WHEN LOWER(REPLACE(TRIM(COALESCE(jik.status_kehadiran, '')), ' ', '_')) IN ('tidak_respon', 'tidakrespon')
                            THEN 1 ELSE 0
                        END
                    ) as tidak_respon
                "),

                DB::raw("
                    SUM(
                        CASE
                            WHEN LOWER(TRIM(COALESCE(jik.status_kehadiran, ''))) = 'reschedule'
                            THEN 1 ELSE 0
                        END
                    ) as reschedule
                "),

                DB::raw("
                    SUM(
                        CASE
                            WHEN LOWER(REPLACE(REPLACE(TRIM(COALESCE(jik.hasil_interview, '')), '-', ' '), '_', ' ')) IN ('lolos interview', 'lolos', 'diterima')
                            THEN 1 ELSE 0
                        END
                    ) as lolos_interview
                "),

                DB::raw("
                    SUM(
                        CASE
                            WHEN LOWER(REPLACE(REPLACE(TRIM(COALESCE(jik.hasil_interview, '')), '-', ' '), '_', ' ')) IN ('tidak lolos interview', 'tidak lolos', 'gagal')
                            THEN 1 ELSE 0
                        END
                    ) as tidak_lolos_interview
                "),

                DB::raw("
                    SUM(
                        CASE
                            WHEN LOWER(TRIM(COALESCE(jik.hasil_interview, ''))) = 'dipertimbangkan'
                            THEN 1 ELSE 0
                        END
                    ) as dipertimbangkan
                "),

                DB::raw("
                    SUM(
                        CASE
                            WHEN jik.id IS NOT NULL
                                AND (
                                    jik.hasil_interview IS NULL
                                    OR TRIM(COALESCE(jik.hasil_interview, '')) = ''
                                )
                            THEN 1 ELSE 0
                        END
                    ) as belum_hasil
                "),

                DB::raw('MAX(ji.jadwal_interview) as interview_terakhir'),
            ])
            ->groupBy('i.id', 'i.nama', 'i.no_wa');
    }

    private function buildDashboard($rows): array
    {
        $totalInterviewer = $rows->count();
        $totalJadwal = $rows->sum('total_jadwal');
        $totalKandidat = $rows->sum('total_kandidat');
        $totalHadir = $rows->sum('hadir');
        $totalLolos = $rows->sum('lolos_interview');

        return [
            'summary' => [
                'total_interviewer' => $totalInterviewer,
                'total_jadwal' => $totalJadwal,
                'total_kandidat' => $totalKandidat,
                'total_hadir' => $totalHadir,
                'total_lolos_interview' => $totalLolos,
                'rata_rata_kandidat_per_interviewer' => $totalInterviewer > 0
                    ? round($totalKandidat / $totalInterviewer, 1)
                    : 0,
            ],
            'demografi' => [
                'top_interviewer' => $rows
                    ->sortByDesc('total_kandidat')
                    ->take(10)
                    ->map(fn ($item) => [
                        'label' => $item['nama'],
                        'total' => (int) $item['total_kandidat'],
                    ])
                    ->values()
                    ->all(),

                'jadwal_interviewer' => $rows
                    ->sortByDesc('total_jadwal')
                    ->take(10)
                    ->map(fn ($item) => [
                        'label' => $item['nama'],
                        'total' => (int) $item['total_jadwal'],
                    ])
                    ->values()
                    ->all(),

                'hasil_interview' => [
                    [
                        'label' => 'Lolos Interview',
                        'total' => (int) $rows->sum('lolos_interview'),
                    ],
                    [
                        'label' => 'Tidak Lolos Interview',
                        'total' => (int) $rows->sum('tidak_lolos_interview'),
                    ],
                    [
                        'label' => 'Dipertimbangkan',
                        'total' => (int) $rows->sum('dipertimbangkan'),
                    ],
                    [
                        'label' => 'Belum Hasil',
                        'total' => (int) $rows->sum('belum_hasil'),
                    ],
                ],

                'status_kehadiran' => [
                    [
                        'label' => 'Hadir',
                        'total' => (int) $rows->sum('hadir'),
                    ],
                    [
                        'label' => 'Tidak Hadir',
                        'total' => (int) $rows->sum('tidak_hadir'),
                    ],
                    [
                        'label' => 'Tidak Respon',
                        'total' => (int) $rows->sum('tidak_respon'),
                    ],
                    [
                        'label' => 'Reschedule',
                        'total' => (int) $rows->sum('reschedule'),
                    ],
                ],
            ],
        ];
    }

    private function formatRow($item): array
    {
        return [
            'id' => $item->id,
            'nama' => $item->nama ?: '-',
            'no_wa' => $item->no_wa ?: '-',
            'total_jadwal' => (int) $item->total_jadwal,
            'total_kandidat' => (int) $item->total_kandidat,
            'hadir' => (int) $item->hadir,
            'tidak_hadir' => (int) $item->tidak_hadir,
            'tidak_respon' => (int) $item->tidak_respon,
            'reschedule' => (int) $item->reschedule,
            'lolos_interview' => (int) $item->lolos_interview,
            'tidak_lolos_interview' => (int) $item->tidak_lolos_interview,
            'dipertimbangkan' => (int) $item->dipertimbangkan,
            'belum_hasil' => (int) $item->belum_hasil,
            'interview_terakhir' => $item->interview_terakhir
                ? date('Y-m-d H:i:s', strtotime($item->interview_terakhir))
                : null,
        ];
    }

    private function hasColumn(string $table, string $column): bool
    {
        try {
            return DB::getSchemaBuilder()->hasColumn($table, $column);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
