<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\JadwalOfferingLetter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportOfferingLetterController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tanggal_awal' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_awal'],
            'status_jadwal' => ['nullable', 'in:semua,pending,menerima,menolak,tidak_melanjutkan'],
            'metode' => ['nullable', 'in:semua,online,offline'],
        ], [
            'tanggal_awal.date' => 'Tanggal awal tidak valid.',
            'tanggal_akhir.date' => 'Tanggal akhir tidak valid.',
            'tanggal_akhir.after_or_equal' => 'Tanggal akhir tidak boleh lebih kecil dari tanggal awal.',
            'status_jadwal.in' => 'Status Offering Letter tidak valid.',
            'metode.in' => 'Metode Offering Letter tidak valid.',
        ]);

        $query = $this->baseQuery($validated);

        $summaryItems = (clone $query)->get();

        $summary = [
            'total' => $summaryItems->count(),
            'pending' => $summaryItems->filter(fn ($item) => $this->normalizeStatus($item->status_jadwal) === null)->count(),
            'menerima' => $summaryItems->filter(fn ($item) => $this->normalizeStatus($item->status_jadwal) === 'menerima')->count(),
            'menolak' => $summaryItems->filter(fn ($item) => $this->normalizeStatus($item->status_jadwal) === 'menolak')->count(),
            'tidak_melanjutkan' => $summaryItems->filter(fn ($item) => $this->normalizeStatus($item->status_jadwal) === 'tidak_melanjutkan')->count(),
            'online' => $summaryItems->filter(fn ($item) => $this->normalizeMetode($item->metode) === 'online')->count(),
            'offline' => $summaryItems->filter(fn ($item) => $this->normalizeMetode($item->metode) === 'offline')->count(),
        ];

        $data = $query
            ->orderByDesc('tanggal_ol')
            ->orderByDesc('jam_ol')
            ->paginate(25);

        $data->getCollection()->transform(fn ($item) => $this->formatRow($item));

        return response()->json([
            'success' => true,
            'message' => 'Report Offering Letter berhasil ditampilkan.',
            'summary' => $summary,
            'data' => $data,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'tanggal_awal' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_awal'],
            'status_jadwal' => ['nullable', 'in:semua,pending,menerima,menolak,tidak_melanjutkan'],
            'metode' => ['nullable', 'in:semua,online,offline'],
        ], [
            'tanggal_awal.date' => 'Tanggal awal tidak valid.',
            'tanggal_akhir.date' => 'Tanggal akhir tidak valid.',
            'tanggal_akhir.after_or_equal' => 'Tanggal akhir tidak boleh lebih kecil dari tanggal awal.',
        ]);

        $rows = $this->baseQuery($validated)
            ->orderByDesc('tanggal_ol')
            ->orderByDesc('jam_ol')
            ->get()
            ->map(fn ($item) => $this->formatRow($item));

        $tanggalAwal = $validated['tanggal_awal'] ?? 'Semua';
        $tanggalAkhir = $validated['tanggal_akhir'] ?? 'Semua';

        $filename = 'report-offering-letter-' . now()->format('Ymd-His') . '.xls';

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

            echo '<div class="title">Report Offering Letter</div>';
            echo '<div class="subtitle">Tanggal Offering Letter: ' . e($tanggalAwal) . ' s/d ' . e($tanggalAkhir) . '</div>';

            echo '<table>';
            echo '<thead>';
            echo '<tr>';
            echo '<th>No</th>';
            echo '<th>Nama Kandidat</th>';
            echo '<th>Email</th>';
            echo '<th>No WA</th>';
            echo '<th>Posisi Dilamar</th>';
            echo '<th>Tanggal OL</th>';
            echo '<th>Jam OL</th>';
            echo '<th>Metode</th>';
            echo '<th>Link</th>';
            echo '<th>PIC</th>';
            echo '<th>Status Jadwal</th>';
            echo '<th>Review Management</th>';
            echo '<th>Catatan</th>';
            echo '<th>Created At</th>';
            echo '<th>Updated At</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            if ($rows->count() === 0) {
                echo '<tr>';
                echo '<td colspan="15" style="text-align:center;">Data tidak ditemukan</td>';
                echo '</tr>';
            }

            foreach ($rows as $index => $row) {
                echo '<tr>';
                echo '<td>' . ($index + 1) . '</td>';
                echo '<td>' . e($row['nama_kandidat']) . '</td>';
                echo '<td>' . e($row['email']) . '</td>';
                echo '<td>' . e($row['no_wa']) . '</td>';
                echo '<td>' . e($row['posisi_dilamar']) . '</td>';
                echo '<td>' . e($row['tanggal_ol']) . '</td>';
                echo '<td>' . e($row['jam_ol']) . '</td>';
                echo '<td>' . e($row['metode']) . '</td>';
                echo '<td>' . e($row['link']) . '</td>';
                echo '<td>' . e($row['pic']) . '</td>';
                echo '<td>' . e($row['status_jadwal_label']) . '</td>';
                echo '<td>' . e($row['review_management']) . '</td>';
                echo '<td>' . e($row['catatan']) . '</td>';
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
        $query = JadwalOfferingLetter::query()
            ->with([
                'hasilReviewManagement.hasilInterview.kandidat',
            ]);

        if (!empty($filters['tanggal_awal'])) {
            $query->whereDate(
                'tanggal_ol',
                '>=',
                Carbon::parse($filters['tanggal_awal'])->toDateString()
            );
        }

        if (!empty($filters['tanggal_akhir'])) {
            $query->whereDate(
                'tanggal_ol',
                '<=',
                Carbon::parse($filters['tanggal_akhir'])->toDateString()
            );
        }

        $statusJadwal = $filters['status_jadwal'] ?? 'semua';

        if ($statusJadwal === 'pending') {
            $query->where(function ($q) {
                $q->whereNull('status_jadwal')
                    ->orWhereRaw("TRIM(COALESCE(status_jadwal, '')) = ''");
            });
        }

        if ($statusJadwal === 'menerima') {
            $query->whereRaw("LOWER(TRIM(COALESCE(status_jadwal, ''))) = 'menerima'");
        }

        if ($statusJadwal === 'menolak') {
            $query->whereRaw("LOWER(TRIM(COALESCE(status_jadwal, ''))) = 'menolak'");
        }

        if ($statusJadwal === 'tidak_melanjutkan') {
            $query->whereRaw("LOWER(REPLACE(TRIM(COALESCE(status_jadwal, '')), ' ', '_')) = 'tidak_melanjutkan'");
        }

        $metode = $filters['metode'] ?? 'semua';

        if ($metode === 'online') {
            $query->whereRaw("LOWER(TRIM(COALESCE(metode, ''))) = 'online'");
        }

        if ($metode === 'offline') {
            $query->whereRaw("LOWER(TRIM(COALESCE(metode, ''))) = 'offline'");
        }

        return $query;
    }

    private function formatRow(JadwalOfferingLetter $item): array
    {
        $hasilReview = $item->hasilReviewManagement;
        $hasilInterview = $hasilReview?->hasilInterview;
        $kandidat = $hasilInterview?->kandidat;

        $status = $this->normalizeStatus($item->status_jadwal);
        $metode = $this->normalizeMetode($item->metode);

        return [
            'id' => $item->id,
            'hasil_review_management_id' => $item->hasil_review_management_id,

            'nama_kandidat' => $kandidat?->nama_lengkap ?: '-',
            'nama_panggil' => $kandidat?->nama_panggil ?: '-',
            'email' => $kandidat?->email ?: '-',
            'no_wa' => $kandidat?->no_wa ?: '-',
            'posisi_dilamar' => $this->getPosisiDilamar($kandidat),

            'tanggal_ol' => $item->tanggal_ol ? date('Y-m-d', strtotime($item->tanggal_ol)) : null,
            'jam_ol' => $item->jam_ol ? substr((string) $item->jam_ol, 0, 5) : '-',

            'metode' => $item->metode ?: '-',
            'metode_normalized' => $metode,
            'link' => $item->link ?: '-',
            'pic' => $item->pic ?: '-',
            'catatan' => $item->catatan ?: '-',

            'status_jadwal' => $status,
            'status_jadwal_label' => $this->labelStatus($status),

            'review_management' => $hasilReview?->review_management ?: '-',
            'status_review_management' => $hasilReview?->status ?: '-',

            'created_at' => $item->created_at ? date('Y-m-d H:i:s', strtotime($item->created_at)) : null,
            'updated_at' => $item->updated_at ? date('Y-m-d H:i:s', strtotime($item->updated_at)) : null,
        ];
    }

    private function getPosisiDilamar($kandidat): string
    {
        if (!$kandidat) {
            return '-';
        }

        if (!empty($kandidat->posisi_dilamar)) {
            return $kandidat->posisi_dilamar;
        }

        if (!empty($kandidat->posisi_yang_dilamar)) {
            return (string) $kandidat->posisi_yang_dilamar;
        }

        if (!empty($kandidat->posisi?->nama_posisi)) {
            return $kandidat->posisi->nama_posisi;
        }

        if (!empty($kandidat->posisi?->posisi)) {
            return $kandidat->posisi->posisi;
        }

        return '-';
    }

    private function normalizeStatus($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = strtolower(trim((string) $value));
        $normalized = str_replace([' ', '-'], '_', $normalized);

        return match ($normalized) {
            'menerima', 'terima', 'diterima', 'accept', 'accepted' => 'menerima',
            'menolak', 'tolak', 'ditolak', 'reject', 'rejected' => 'menolak',
            'tidak_melanjutkan', 'tidakmelanjutkan', 'tidak_lanjut', 'tidak_lanjutkan' => 'tidak_melanjutkan',
            default => null,
        };
    }

    private function normalizeMetode($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = strtolower(trim((string) $value));

        return match ($normalized) {
            'online' => 'online',
            'offline' => 'offline',
            default => null,
        };
    }

    private function labelStatus(?string $value): string
    {
        return match ($value) {
            'menerima' => 'Menerima',
            'menolak' => 'Menolak',
            'tidak_melanjutkan' => 'Tidak Melanjutkan',
            default => 'Pending',
        };
    }
}