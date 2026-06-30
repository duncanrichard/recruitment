<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportHasilTestMmpiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tanggal_awal' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_awal'],
            'status_kehadiran' => ['nullable', 'in:semua,hadir,tidak_hadir,belum_ada'],
            'hasil_test' => ['nullable', 'in:semua,lolos,gagal,belum_ada'],
        ], [
            'tanggal_awal.date' => 'Tanggal awal tidak valid.',
            'tanggal_akhir.date' => 'Tanggal akhir tidak valid.',
            'tanggal_akhir.after_or_equal' => 'Tanggal akhir tidak boleh lebih kecil dari tanggal awal.',
        ]);

        $rows = $this->baseQuery($validated)
            ->orderByDesc('dh.tanggal_kehadiran')
            ->orderByDesc('jtm.tanggal')
            ->paginate(25);

        $rows->getCollection()->transform(fn ($item) => $this->formatRow($item));

        $dashboardRows = $this->baseQuery($validated)
            ->orderByDesc('dh.tanggal_kehadiran')
            ->orderByDesc('jtm.tanggal')
            ->get()
            ->map(fn ($item) => $this->formatRow($item));

        return response()->json([
            'success' => true,
            'message' => 'Report hasil test MMPI berhasil ditampilkan.',
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
            'status_kehadiran' => ['nullable', 'in:semua,hadir,tidak_hadir,belum_ada'],
            'hasil_test' => ['nullable', 'in:semua,lolos,gagal,belum_ada'],
        ], [
            'tanggal_awal.date' => 'Tanggal awal tidak valid.',
            'tanggal_akhir.date' => 'Tanggal akhir tidak valid.',
            'tanggal_akhir.after_or_equal' => 'Tanggal akhir tidak boleh lebih kecil dari tanggal awal.',
        ]);

        $rows = $this->baseQuery($validated)
            ->orderByDesc('dh.tanggal_kehadiran')
            ->orderByDesc('jtm.tanggal')
            ->get()
            ->map(fn ($item) => $this->formatRow($item));

        $tanggalAwal = $validated['tanggal_awal'] ?? 'Semua';
        $tanggalAkhir = $validated['tanggal_akhir'] ?? 'Semua';
        $filename = 'report-hasil-test-mmpi-' . now()->format('Ymd-His') . '.xls';

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

            echo '<div class="title">Report Hasil Test MMPI</div>';
            echo '<div class="subtitle">Tanggal Kehadiran: ' . e($tanggalAwal) . ' s/d ' . e($tanggalAkhir) . '</div>';

            echo '<table>';
            echo '<thead>';
            echo '<tr>';
            echo '<th>No</th>';
            echo '<th>Tanggal Kehadiran</th>';
            echo '<th>Tanggal Jadwal MMPI</th>';
            echo '<th>Token</th>';
            echo '<th>Nama Pelamar</th>';
            echo '<th>Email</th>';
            echo '<th>No HP / WA</th>';
            echo '<th>Status Kehadiran</th>';
            echo '<th>Hasil Test</th>';
            echo '<th>Jadwal Zoom</th>';
            echo '<th>Created At</th>';
            echo '<th>Updated At</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            if ($rows->count() === 0) {
                echo '<tr>';
                echo '<td colspan="12" style="text-align:center;">Data tidak ditemukan</td>';
                echo '</tr>';
            }

            foreach ($rows as $index => $row) {
                echo '<tr>';
                echo '<td>' . ($index + 1) . '</td>';
                echo '<td>' . e($row['tanggal_kehadiran']) . '</td>';
                echo '<td>' . e($row['tanggal_mmpi']) . '</td>';
                echo '<td>' . e($row['token']) . '</td>';
                echo '<td>' . e($row['nama']) . '</td>';
                echo '<td>' . e($row['email']) . '</td>';
                echo '<td>' . e($row['no_hp']) . '</td>';
                echo '<td>' . e($row['status_kehadiran_label']) . '</td>';
                echo '<td>' . e($row['hasil_test_label']) . '</td>';
                echo '<td>' . e($row['jadwal_zoom']) . '</td>';
                echo '<td>' . e($row['created_at']) . '</td>';
                echo '<td>' . e($row['updated_at']) . '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
            echo '</body>';
            echo '</html>';
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    private function baseQuery(array $filters)
    {
        $query = DB::table('daftar_hadir_test_mmpi as dh')
            ->leftJoin('jadwal_test_mmpi as jtm', 'jtm.id', '=', 'dh.jadwal_test_mmpi_id')
            ->leftJoin('daftar_hadir_test_zoom as dhz', 'dhz.id', '=', 'jtm.daftar_hadir_test_zoom_id')
            ->leftJoin('jadwal_test_zoom as jtz', 'jtz.id', '=', 'dhz.jadwal_test_zoom_id')
            ->leftJoin('data_riwayat_diri as drd', 'drd.id', '=', 'dh.data_riwayat_diri_id')
            ->whereNull('dh.deleted_at')
            ->select([
                'dh.id',
                'dh.data_riwayat_diri_id',
                'dh.jadwal_test_mmpi_id',
                'dh.tanggal_kehadiran',
                'dh.status_kehadiran',
                'dh.hasil_test',
                'dh.created_at',
                'dh.updated_at',

                'jtm.tanggal as tanggal_mmpi',
                'jtz.jadwal as jadwal_zoom',

                'drd.nama_lengkap',
                'drd.nama_panggil',
                'drd.email',
                'drd.no_wa',
                'drd.token',
            ]);

        if (!empty($filters['tanggal_awal'])) {
            $query->whereDate(
                'dh.tanggal_kehadiran',
                '>=',
                Carbon::parse($filters['tanggal_awal'])->toDateString()
            );
        }

        if (!empty($filters['tanggal_akhir'])) {
            $query->whereDate(
                'dh.tanggal_kehadiran',
                '<=',
                Carbon::parse($filters['tanggal_akhir'])->toDateString()
            );
        }

        $statusKehadiran = $filters['status_kehadiran'] ?? 'semua';

        if ($statusKehadiran === 'hadir') {
            $query->whereRaw("LOWER(COALESCE(dh.status_kehadiran, '')) = 'hadir'");
        }

        if ($statusKehadiran === 'tidak_hadir') {
            $query->whereRaw("LOWER(REPLACE(REPLACE(COALESCE(dh.status_kehadiran, ''), ' ', '_'), '-', '_')) IN ('tidak_hadir', 'tidakhadir', 'tidak')");
        }

        if ($statusKehadiran === 'belum_ada') {
            $query->where(function ($q) {
                $q->whereNull('dh.status_kehadiran')
                    ->orWhereRaw("TRIM(COALESCE(dh.status_kehadiran, '')) = ''");
            });
        }

        $hasilTest = $filters['hasil_test'] ?? 'semua';

        if ($hasilTest === 'lolos') {
            $query->whereRaw("LOWER(COALESCE(dh.hasil_test, '')) = 'lolos'");
        }

        if ($hasilTest === 'gagal') {
            $query->whereRaw("LOWER(COALESCE(dh.hasil_test, '')) = 'gagal'");
        }

        if ($hasilTest === 'belum_ada') {
            $query->where(function ($q) {
                $q->whereNull('dh.hasil_test')
                    ->orWhereRaw("TRIM(COALESCE(dh.hasil_test, '')) = ''");
            });
        }

        return $query;
    }

    private function buildDashboard(Collection $rows): array
    {
        $total = $rows->count();

        $hadir = $rows->where('status_kehadiran', 'hadir')->count();
        $tidakHadir = $rows->where('status_kehadiran', 'tidak_hadir')->count();
        $belumKehadiran = $rows->filter(fn ($item) => empty($item['status_kehadiran']))->count();

        $lolos = $rows->where('hasil_test', 'lolos')->count();
        $gagal = $rows->where('hasil_test', 'gagal')->count();
        $belumHasil = $rows->filter(fn ($item) => empty($item['hasil_test']))->count();

        return [
            'summary' => [
                'total' => $total,
                'hadir' => $hadir,
                'tidak_hadir' => $tidakHadir,
                'belum_ada_kehadiran' => $belumKehadiran,
                'lolos' => $lolos,
                'gagal' => $gagal,
                'belum_ada_hasil' => $belumHasil,
                'persentase_hadir' => $total > 0 ? round(($hadir / $total) * 100, 1) : 0,
                'persentase_lolos' => $total > 0 ? round(($lolos / $total) * 100, 1) : 0,
            ],
            'demografi' => [
                'status_kehadiran' => $this->groupByValue($rows, 'status_kehadiran_label', 'Belum Ada'),
                'hasil_test' => $this->groupByValue($rows, 'hasil_test_label', 'Belum Ada'),
                'kehadiran_hasil' => $this->groupByKehadiranHasil($rows),
                'domain_email' => $this->groupByEmailDomain($rows),
                'kelengkapan_kontak' => $this->groupByContactCompleteness($rows),
            ],
            'top' => [
                'tanggal_mmpi' => $this->groupByMmpiDate($rows),
                'peserta' => $this->groupByValue($rows, 'nama', 'Tidak Diisi', 7),
            ],
            'trend' => $this->groupByDate($rows),
        ];
    }

    private function groupByValue(
        Collection $rows,
        string $key,
        string $emptyLabel = 'Tidak Diisi',
        int $limit = 10
    ): array {
        return $rows
            ->map(function ($item) use ($key, $emptyLabel) {
                $value = trim((string) ($item[$key] ?? ''));

                return $value !== '' && $value !== '-' ? $value : $emptyLabel;
            })
            ->countBy()
            ->sortDesc()
            ->take($limit)
            ->map(function ($total, $label) {
                return [
                    'label' => (string) $label,
                    'total' => (int) $total,
                ];
            })
            ->values()
            ->all();
    }

    private function groupByDate(Collection $rows): array
    {
        return $rows
            ->map(function ($item) {
                $tanggal = $item['tanggal_kehadiran'] ?? null;

                if (!$tanggal) {
                    return 'Tidak Diisi';
                }

                try {
                    return Carbon::parse($tanggal)->format('Y-m-d');
                } catch (\Throwable $e) {
                    return (string) $tanggal;
                }
            })
            ->countBy()
            ->sortKeys()
            ->map(function ($total, $label) {
                return [
                    'label' => (string) $label,
                    'total' => (int) $total,
                ];
            })
            ->values()
            ->all();
    }

    private function groupByMmpiDate(Collection $rows): array
    {
        return $rows
            ->map(function ($item) {
                $tanggal = $item['tanggal_mmpi'] ?? null;

                if (!$tanggal) {
                    return 'Tidak Diisi';
                }

                try {
                    return Carbon::parse($tanggal)->format('d M Y');
                } catch (\Throwable $e) {
                    return (string) $tanggal;
                }
            })
            ->countBy()
            ->sortDesc()
            ->take(7)
            ->map(function ($total, $label) {
                return [
                    'label' => (string) $label,
                    'total' => (int) $total,
                ];
            })
            ->values()
            ->all();
    }

    private function groupByKehadiranHasil(Collection $rows): array
    {
        return $rows
            ->map(function ($item) {
                $kehadiran = $item['status_kehadiran_label'] ?: 'Belum Ada Kehadiran';
                $hasil = $item['hasil_test_label'] ?: 'Belum Ada Hasil';

                return $kehadiran . ' - ' . $hasil;
            })
            ->countBy()
            ->sortDesc()
            ->take(10)
            ->map(function ($total, $label) {
                return [
                    'label' => (string) $label,
                    'total' => (int) $total,
                ];
            })
            ->values()
            ->all();
    }

    private function groupByEmailDomain(Collection $rows): array
    {
        return $rows
            ->map(function ($item) {
                $email = trim((string) ($item['email'] ?? ''));

                if ($email === '' || $email === '-' || !str_contains($email, '@')) {
                    return 'Tidak Diisi';
                }

                return strtolower(substr(strrchr($email, '@'), 1));
            })
            ->countBy()
            ->sortDesc()
            ->take(10)
            ->map(function ($total, $label) {
                return [
                    'label' => (string) $label,
                    'total' => (int) $total,
                ];
            })
            ->values()
            ->all();
    }

    private function groupByContactCompleteness(Collection $rows): array
    {
        $groups = [
            'Email & No HP Lengkap' => 0,
            'Email Saja' => 0,
            'No HP Saja' => 0,
            'Tidak Lengkap' => 0,
        ];

        foreach ($rows as $item) {
            $email = trim((string) ($item['email'] ?? ''));
            $phone = trim((string) ($item['no_hp'] ?? ''));

            $hasEmail = $email !== '' && $email !== '-';
            $hasPhone = $phone !== '' && $phone !== '-';

            if ($hasEmail && $hasPhone) {
                $groups['Email & No HP Lengkap']++;
            } elseif ($hasEmail) {
                $groups['Email Saja']++;
            } elseif ($hasPhone) {
                $groups['No HP Saja']++;
            } else {
                $groups['Tidak Lengkap']++;
            }
        }

        return collect($groups)
            ->filter(fn ($total) => $total > 0)
            ->map(function ($total, $label) {
                return [
                    'label' => (string) $label,
                    'total' => (int) $total,
                ];
            })
            ->values()
            ->all();
    }

    private function formatRow($item): array
    {
        $statusKehadiran = $this->normalizeKehadiran($item->status_kehadiran ?? null);
        $hasilTest = $this->normalizeHasilTest($item->hasil_test ?? null);

        return [
            'id' => $item->id,
            'data_riwayat_diri_id' => $item->data_riwayat_diri_id,
            'jadwal_test_mmpi_id' => $item->jadwal_test_mmpi_id,
            'token' => $item->token,

            'tanggal_kehadiran' => $item->tanggal_kehadiran,
            'tanggal_mmpi' => $item->tanggal_mmpi,
            'jadwal_zoom' => $item->jadwal_zoom ? date('Y-m-d H:i:s', strtotime($item->jadwal_zoom)) : null,

            'nama' => $item->nama_lengkap ?: '-',
            'nama_panggil' => $item->nama_panggil ?: '-',
            'email' => $item->email ?: '-',
            'no_hp' => $item->no_wa ?: '-',

            'status_kehadiran' => $statusKehadiran,
            'status_kehadiran_label' => $this->labelKehadiran($statusKehadiran),

            'hasil_test' => $hasilTest,
            'hasil_test_label' => $this->labelHasilTest($hasilTest),

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

        if (in_array($normalized, ['hadir', '1', 'true', 'ya', 'yes'], true)) {
            return 'hadir';
        }

        if (in_array($normalized, ['tidak_hadir', 'tidakhadir', 'tidak', '0', 'false', 'no'], true)) {
            return 'tidak_hadir';
        }

        return null;
    }

    private function normalizeHasilTest($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = strtolower(trim((string) $value));
        $normalized = str_replace([' ', '-'], '_', $normalized);

        if (in_array($normalized, ['lolos', 'lulus', 'passed', 'pass', '1', 'true', 'ya', 'yes'], true)) {
            return 'lolos';
        }

        if (in_array($normalized, ['gagal', 'tidak_lolos', 'tidaklolos', 'tidak', 'failed', 'fail', '0', 'false', 'no'], true)) {
            return 'gagal';
        }

        return null;
    }

    private function labelKehadiran(?string $value): string
    {
        return match ($value) {
            'hadir' => 'Hadir',
            'tidak_hadir' => 'Tidak Hadir',
            default => 'Belum Ada',
        };
    }

    private function labelHasilTest(?string $value): string
    {
        return match ($value) {
            'lolos' => 'Lolos',
            'gagal' => 'Gagal',
            default => 'Belum Ada',
        };
    }
}