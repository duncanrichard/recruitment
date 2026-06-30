<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\DataRiwayatDiri;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportDataPelamarController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tanggal_awal' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_awal'],
            'perusahaan' => ['nullable', 'string', 'max:255'],
        ], [
            'tanggal_awal.date' => 'Tanggal awal tidak valid.',
            'tanggal_akhir.date' => 'Tanggal akhir tidak valid.',
            'tanggal_akhir.after_or_equal' => 'Tanggal akhir tidak boleh lebih kecil dari tanggal awal.',
        ]);

        $rows = $this->queryPelamar($validated)
            ->orderByDesc('tanggal_skrining')
            ->orderByDesc('created_at')
            ->paginate(25);

        $rows->getCollection()->transform(function ($item) {
            return $this->formatPelamar($item);
        });

        $dashboardRows = $this->queryPelamar($validated)
            ->orderByDesc('tanggal_skrining')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($item) => $this->formatPelamar($item));

        return response()->json([
            'success' => true,
            'message' => 'Data report pelamar berhasil ditampilkan.',
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
            'perusahaan' => ['nullable', 'string', 'max:255'],
        ], [
            'tanggal_awal.date' => 'Tanggal awal tidak valid.',
            'tanggal_akhir.date' => 'Tanggal akhir tidak valid.',
            'tanggal_akhir.after_or_equal' => 'Tanggal akhir tidak boleh lebih kecil dari tanggal awal.',
        ]);

        $rows = $this->queryPelamar($validated)
            ->orderByDesc('tanggal_skrining')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($item) => $this->formatPelamar($item));

        $tanggalAwal = $validated['tanggal_awal'] ?? 'Semua';
        $tanggalAkhir = $validated['tanggal_akhir'] ?? 'Semua';
        $perusahaan = $validated['perusahaan'] ?? 'Semua';
        $filename = 'report-data-pelamar-' . now()->format('Ymd-His') . '.xls';

        return response()->streamDownload(function () use ($rows, $tanggalAwal, $tanggalAkhir, $perusahaan) {
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

            echo '<div class="title">Report Data Pelamar</div>';
            echo '<div class="subtitle">';
            echo 'Tanggal Skrining: ' . e($tanggalAwal) . ' s/d ' . e($tanggalAkhir);
            echo '<br>';
            echo 'Perusahaan: ' . e($perusahaan);
            echo '</div>';

            echo '<table>';
            echo '<thead>';
            echo '<tr>';
            echo '<th>No</th>';
            echo '<th>Token</th>';
            echo '<th>Tanggal Skrining</th>';
            echo '<th>Nama Lengkap</th>';
            echo '<th>Nama Panggil</th>';
            echo '<th>Email</th>';
            echo '<th>No WA</th>';
            echo '<th>Posisi</th>';
            echo '<th>Perusahaan</th>';
            echo '<th>Pendidikan</th>';
            echo '<th>Jurusan</th>';
            echo '<th>Nama Institusi</th>';
            echo '<th>Agama</th>';
            echo '<th>Tanggal Lahir</th>';
            echo '<th>Tempat Lahir</th>';
            echo '<th>Jenis Kelamin</th>';
            echo '<th>Alamat KTP</th>';
            echo '<th>Alamat Domisili</th>';
            echo '<th>Kewarganegaraan</th>';
            echo '<th>Status Pernikahan</th>';
            echo '<th>Gol Darah</th>';
            echo '<th>Tinggi Badan</th>';
            echo '<th>Berat Badan</th>';
            echo '<th>STR Aktif</th>';
            echo '<th>Sumber Informasi</th>';
            echo '<th>Hasil Administrasi</th>';
            echo '<th>Created At</th>';
            echo '<th>Updated At</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            if ($rows->count() === 0) {
                echo '<tr>';
                echo '<td colspan="28" style="text-align:center;">Data tidak ditemukan</td>';
                echo '</tr>';
            }

            foreach ($rows as $index => $row) {
                echo '<tr>';
                echo '<td>' . ($index + 1) . '</td>';
                echo '<td>' . e($row['token']) . '</td>';
                echo '<td>' . e($row['tanggal_skrining']) . '</td>';
                echo '<td>' . e($row['nama_lengkap']) . '</td>';
                echo '<td>' . e($row['nama_panggil']) . '</td>';
                echo '<td>' . e($row['email']) . '</td>';
                echo '<td>' . e($row['no_wa']) . '</td>';
                echo '<td>' . e($row['posisi_yang_dilamar']) . '</td>';
                echo '<td>' . e($row['perusahaan_dilamar']) . '</td>';
                echo '<td>' . e($row['pendidikan']) . '</td>';
                echo '<td>' . e($row['jurusan']) . '</td>';
                echo '<td>' . e($row['nama_institusi']) . '</td>';
                echo '<td>' . e($row['agama']) . '</td>';
                echo '<td>' . e($row['tanggal_lahir']) . '</td>';
                echo '<td>' . e($row['tempat_lahir']) . '</td>';
                echo '<td>' . e($row['jenis_kelamin']) . '</td>';
                echo '<td>' . e($row['alamat_ktp']) . '</td>';
                echo '<td>' . e($row['alamat_domisili']) . '</td>';
                echo '<td>' . e($row['kewarganegaraan']) . '</td>';
                echo '<td>' . e($row['status_pernikahan']) . '</td>';
                echo '<td>' . e($row['gol_darah']) . '</td>';
                echo '<td>' . e($row['tinggi_badan']) . '</td>';
                echo '<td>' . e($row['berat_badan']) . '</td>';
                echo '<td>' . e($row['str_aktif']) . '</td>';
                echo '<td>' . e($row['sumber_informasi']) . '</td>';
                echo '<td>' . e($row['hasil_administrasi']) . '</td>';
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

    private function queryPelamar(array $filters)
    {
        $query = DataRiwayatDiri::query()
            ->with([
                'posisi',
                'perusahaan',
                'pendidikan',
                'agama',
                'kewarganegaraan',
                'statusPernikahan',
                'sumberInformasi',
            ]);

        if (!empty($filters['tanggal_awal'])) {
            $query->whereDate(
                'tanggal_skrining',
                '>=',
                Carbon::parse($filters['tanggal_awal'])->toDateString()
            );
        }

        if (!empty($filters['tanggal_akhir'])) {
            $query->whereDate(
                'tanggal_skrining',
                '<=',
                Carbon::parse($filters['tanggal_akhir'])->toDateString()
            );
        }

        if (!empty($filters['perusahaan'])) {
            $keyword = $filters['perusahaan'];

            $query->where(function ($q) use ($keyword) {
                $q->where('perusahaan_dilamar', 'like', '%' . $keyword . '%')
                    ->orWhereHas('perusahaan', function ($perusahaanQuery) use ($keyword) {
                        $perusahaanQuery->where('nama_perusahaan', 'like', '%' . $keyword . '%')
                            ->orWhere('perusahaan', 'like', '%' . $keyword . '%')
                            ->orWhere('nama', 'like', '%' . $keyword . '%');
                    });
            });
        }

        return $query;
    }

    private function buildDashboard(Collection $rows): array
    {
        $totalPelamar = $rows->count();

        $lolos = $rows->filter(function ($item) {
            return $this->isLolos($item['hasil_administrasi'] ?? null);
        })->count();

        $tidakLolos = $rows->filter(function ($item) {
            return $this->isTidakLolos($item['hasil_administrasi'] ?? null);
        })->count();

        $belumDiproses = $rows->filter(function ($item) {
            return empty($item['hasil_administrasi']);
        })->count();

        return [
            'summary' => [
                'total_pelamar' => $totalPelamar,
                'lolos_administrasi' => $lolos,
                'tidak_lolos_administrasi' => $tidakLolos,
                'belum_diproses' => $belumDiproses,
                'persentase_lolos' => $totalPelamar > 0 ? round(($lolos / $totalPelamar) * 100, 1) : 0,
            ],
            'demografi' => [
                'jenis_kelamin' => $this->groupByValue($rows, 'jenis_kelamin'),
                'pendidikan' => $this->groupByValue($rows, 'pendidikan'),
                'agama' => $this->groupByValue($rows, 'agama'),
                'usia' => $this->groupByAge($rows),
                'status_pernikahan' => $this->groupByValue($rows, 'status_pernikahan'),
                'hasil_administrasi' => $this->groupByValue($rows, 'hasil_administrasi', 'Belum Diproses'),
            ],
            'top' => [
                'perusahaan' => $this->groupByValue($rows, 'perusahaan_dilamar', 'Tidak Diisi', 7),
                'posisi' => $this->groupByValue($rows, 'posisi_yang_dilamar', 'Tidak Diisi', 7),
                'sumber_informasi' => $this->groupByValue($rows, 'sumber_informasi', 'Tidak Diisi', 7),
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

                return $value !== '' ? $value : $emptyLabel;
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
                $tanggal = $item['tanggal_skrining'] ?? null;

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

    private function groupByAge(Collection $rows): array
    {
        $groups = [
            '< 20 Tahun' => 0,
            '20 - 25 Tahun' => 0,
            '26 - 30 Tahun' => 0,
            '31 - 35 Tahun' => 0,
            '36 - 40 Tahun' => 0,
            '> 40 Tahun' => 0,
            'Tidak Diisi' => 0,
        ];

        foreach ($rows as $item) {
            $tanggalLahir = $item['tanggal_lahir'] ?? null;

            if (!$tanggalLahir) {
                $groups['Tidak Diisi']++;
                continue;
            }

            try {
                $age = Carbon::parse($tanggalLahir)->age;
            } catch (\Throwable $e) {
                $groups['Tidak Diisi']++;
                continue;
            }

            if ($age < 20) {
                $groups['< 20 Tahun']++;
            } elseif ($age <= 25) {
                $groups['20 - 25 Tahun']++;
            } elseif ($age <= 30) {
                $groups['26 - 30 Tahun']++;
            } elseif ($age <= 35) {
                $groups['31 - 35 Tahun']++;
            } elseif ($age <= 40) {
                $groups['36 - 40 Tahun']++;
            } else {
                $groups['> 40 Tahun']++;
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

    private function isLolos(?string $value): bool
    {
        $normalized = strtolower(trim((string) $value));

        return str_contains($normalized, 'lolos')
            || str_contains($normalized, 'diterima')
            || str_contains($normalized, 'passed')
            || str_contains($normalized, 'pass');
    }

    private function isTidakLolos(?string $value): bool
    {
        $normalized = strtolower(trim((string) $value));

        return str_contains($normalized, 'tidak')
            || str_contains($normalized, 'gagal')
            || str_contains($normalized, 'ditolak')
            || str_contains($normalized, 'failed')
            || str_contains($normalized, 'fail');
    }

    private function formatPelamar(DataRiwayatDiri $item): array
    {
        return [
            'id' => $item->id,
            'token' => $item->token,
            'tanggal_skrining' => optional($item->tanggal_skrining)->format('Y-m-d') ?: $item->tanggal_skrining,

            'nama_lengkap' => $item->nama_lengkap,
            'nama_panggil' => $item->nama_panggil,
            'email' => $item->email,
            'no_wa' => $item->no_wa,

            'posisi_yang_dilamar' => $this->firstValue($item->posisi, [
                'nama_posisi',
                'posisi',
                'nama',
                'jabatan',
            ]) ?: $item->posisi_yang_dilamar,

            'perusahaan_dilamar' => $this->firstValue($item->perusahaan, [
                'nama_perusahaan',
                'perusahaan',
                'nama',
            ]) ?: $item->perusahaan_dilamar,

            'pendidikan' => $this->firstValue($item->pendidikan, [
                'pendidikan',
                'nama_pendidikan',
                'nama',
            ]) ?: $item->pendidikan_id,

            'jurusan' => $item->jurusan,
            'nama_institusi' => $item->nama_institusi,

            'agama' => $this->firstValue($item->agama, [
                'agama',
                'nama_agama',
                'nama',
            ]) ?: $item->agama_id,

            'tanggal_lahir' => optional($item->tanggal_lahir)->format('Y-m-d') ?: $item->tanggal_lahir,
            'tempat_lahir' => $item->tempat_lahir,
            'jenis_kelamin' => $item->jenis_kelamin,

            'alamat_ktp' => $item->alamat_ktp,
            'alamat_domisili' => $item->alamat_domisili,

            'kewarganegaraan' => $this->firstValue($item->kewarganegaraan, [
                'kewarganegaraan',
                'nama_kewarganegaraan',
                'nama',
            ]) ?: $item->kewarganegaraan_id,

            'status_pernikahan' => $this->firstValue($item->statusPernikahan, [
                'status_pernikahan',
                'status',
                'nama',
            ]) ?: $item->status_pernikahan_id,

            'gol_darah' => $item->gol_darah,
            'tinggi_badan' => $item->tinggi_badan,
            'berat_badan' => $item->berat_badan,
            'str_aktif' => $item->str_aktif,

            'sumber_informasi' => $this->firstValue($item->sumberInformasi, [
                'informasi',
                'sumber_informasi',
                'nama',
            ]) ?: $item->sumber_informasi_id,

            'hasil_administrasi' => $item->hasil_administrasi,
            'created_at' => optional($item->created_at)->format('Y-m-d H:i:s'),
            'updated_at' => optional($item->updated_at)->format('Y-m-d H:i:s'),
        ];
    }

    private function firstValue($model, array $columns): ?string
    {
        if (!$model) {
            return null;
        }

        foreach ($columns as $column) {
            if (!empty($model->{$column})) {
                return (string) $model->{$column};
            }
        }

        return null;
    }
}