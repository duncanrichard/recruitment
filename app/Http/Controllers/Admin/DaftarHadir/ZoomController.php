<?php

namespace App\Http\Controllers\Admin\DaftarHadir;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ZoomController extends Controller
{
    public function groups(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
        ]);

        $query = DB::table('jadwal_test_zoom as jtz')
            ->leftJoin('daftar_hadir_test_zoom as dh', function ($join) {
                $join->on('dh.jadwal_test_zoom_id', '=', 'jtz.id')
                    ->whereNull('dh.deleted_at');
            })
            ->whereNotNull('jtz.jadwal')
            ->whereNull('jtz.deleted_at')
            ->selectRaw('DATE(jtz.jadwal) as tanggal_test')
            ->selectRaw('COUNT(DISTINCT jtz.id) as total_peserta')
            ->selectRaw("
                COUNT(DISTINCT CASE
                    WHEN LOWER(COALESCE(dh.status_kehadiran, '')) = 'hadir'
                    THEN jtz.id
                END) as total_hadir
            ")
            ->selectRaw("
                COUNT(DISTINCT CASE
                    WHEN LOWER(COALESCE(dh.status_kehadiran, '')) IN ('tidak_hadir', 'tidak hadir', 'tidakhadir', 'tidak')
                    THEN jtz.id
                END) as total_tidak_hadir
            ")
            ->selectRaw("
                COUNT(DISTINCT CASE
                    WHEN dh.id IS NULL
                      OR dh.status_kehadiran IS NULL
                      OR TRIM(COALESCE(dh.status_kehadiran, '')) = ''
                    THEN jtz.id
                END) as total_belum_ada
            ")
            ->selectRaw("
                COUNT(DISTINCT CASE
                    WHEN LOWER(COALESCE(dh.hasil_test, '')) = 'lolos'
                    THEN jtz.id
                END) as total_lolos
            ")
            ->selectRaw("
                COUNT(DISTINCT CASE
                    WHEN LOWER(COALESCE(dh.hasil_test, '')) = 'gagal'
                    THEN jtz.id
                END) as total_gagal
            ");

        if (!empty($validated['tanggal_mulai'])) {
            $query->whereDate('jtz.jadwal', '>=', $validated['tanggal_mulai']);
        }

        if (!empty($validated['tanggal_selesai'])) {
            $query->whereDate('jtz.jadwal', '<=', $validated['tanggal_selesai']);
        }

        $data = $query
            ->groupBy(DB::raw('DATE(jtz.jadwal)'))
            ->orderByDesc(DB::raw('DATE(jtz.jadwal)'))
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data group daftar hadir Zoom berhasil diambil.',
            'data' => $data,
        ]);
    }

    public function detail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'status' => ['nullable', Rule::in(['semua', 'hadir', 'tidak_hadir'])],
        ]);

        $tanggal = $validated['tanggal'];
        $status = $validated['status'] ?? 'semua';

        $nameColumn = $this->firstExistingColumn('data_riwayat_diri', [
            'nama_lengkap',
            'nama',
            'nama_pelamar',
        ]);

        $phoneColumn = $this->firstExistingColumn('data_riwayat_diri', [
            'no_wa',
            'no_hp',
            'nomor_hp',
            'no_telepon',
            'telepon',
            'phone',
        ]);

        $selects = [
            'jtz.id as id',
            'jtz.id as jadwal_test_zoom_id',
            'jtz.data_riwayat_diri_id',
            'jtz.jadwal',
            'jtz.link_zoom',
            'dh.id as daftar_hadir_test_zoom_id',
            'dh.tanggal_kehadiran',
            'dh.status_kehadiran',
            'dh.hasil_test',
            'drd.email',
        ];

        if ($nameColumn) {
            $selects[] = DB::raw("drd.{$nameColumn} as nama");
        } else {
            $selects[] = DB::raw("'-' as nama");
        }

        if ($phoneColumn) {
            $selects[] = DB::raw("drd.{$phoneColumn} as no_hp");
        } else {
            $selects[] = DB::raw("'-' as no_hp");
        }

        $query = DB::table('jadwal_test_zoom as jtz')
            ->leftJoin('daftar_hadir_test_zoom as dh', function ($join) {
                $join->on('dh.jadwal_test_zoom_id', '=', 'jtz.id')
                    ->whereNull('dh.deleted_at');
            })
            ->leftJoin('data_riwayat_diri as drd', 'drd.id', '=', 'jtz.data_riwayat_diri_id')
            ->whereDate('jtz.jadwal', $tanggal)
            ->whereNull('jtz.deleted_at')
            ->select($selects)
            ->orderBy('jtz.jadwal', 'asc');

        if ($status === 'hadir') {
            $query->whereRaw("LOWER(COALESCE(dh.status_kehadiran, '')) = 'hadir'");
        }

        if ($status === 'tidak_hadir') {
            $query->whereRaw("LOWER(COALESCE(dh.status_kehadiran, '')) IN ('tidak_hadir', 'tidak hadir', 'tidakhadir', 'tidak')");
        }

        $items = $query->get();

        $data = $items->map(function ($item) {
            $statusKehadiran = $this->normalizeKehadiranValue($item->status_kehadiran ?? null);
            $hasilTest = $this->normalizeHasilTestValue($item->hasil_test ?? null);

            return [
                'id' => $item->jadwal_test_zoom_id,
                'jadwal_test_zoom_id' => $item->jadwal_test_zoom_id,
                'daftar_hadir_test_zoom_id' => $item->daftar_hadir_test_zoom_id,
                'data_riwayat_diri_id' => $item->data_riwayat_diri_id,
                'jadwal' => $item->jadwal ? date('Y-m-d H:i:s', strtotime($item->jadwal)) : null,
                'tanggal_kehadiran' => $item->tanggal_kehadiran,
                'kehadiran' => $statusKehadiran,
                'status_kehadiran' => $statusKehadiran,
                'kehadiran_label' => $this->labelKehadiran($statusKehadiran),
                'hasil_test' => $hasilTest,
                'hasil_test_label' => $this->labelHasilTest($hasilTest),
                'link_zoom' => $item->link_zoom,
                'nama' => $item->nama ?: '-',
                'email' => $item->email ?: '-',
                'no_hp' => $item->no_hp ?: '-',
            ];
        });

        $allItems = DB::table('jadwal_test_zoom as jtz')
            ->leftJoin('daftar_hadir_test_zoom as dh', function ($join) {
                $join->on('dh.jadwal_test_zoom_id', '=', 'jtz.id')
                    ->whereNull('dh.deleted_at');
            })
            ->whereDate('jtz.jadwal', $tanggal)
            ->whereNull('jtz.deleted_at')
            ->select([
                'jtz.id',
                'dh.status_kehadiran',
                'dh.hasil_test',
            ])
            ->get();

        $summary = [
            'total' => $allItems->count(),
            'hadir' => $allItems->filter(fn ($item) => $this->normalizeKehadiranValue($item->status_kehadiran ?? null) === 'hadir')->count(),
            'tidak_hadir' => $allItems->filter(fn ($item) => $this->normalizeKehadiranValue($item->status_kehadiran ?? null) === 'tidak_hadir')->count(),
            'belum_ada' => $allItems->filter(fn ($item) => $this->normalizeKehadiranValue($item->status_kehadiran ?? null) === null)->count(),
            'lolos' => $allItems->filter(fn ($item) => $this->normalizeHasilTestValue($item->hasil_test ?? null) === 'lolos')->count(),
            'gagal' => $allItems->filter(fn ($item) => $this->normalizeHasilTestValue($item->hasil_test ?? null) === 'gagal')->count(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Detail daftar hadir Zoom berhasil diambil.',
            'summary' => $summary,
            'data' => $data->values(),
        ]);
    }

    public function updateHasilTest(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'hasil_test' => ['required', Rule::in(['lolos', 'gagal'])],
        ]);

        if (!Schema::hasTable('daftar_hadir_test_zoom')) {
            return response()->json([
                'success' => false,
                'message' => 'Tabel daftar_hadir_test_zoom tidak ditemukan.',
            ], 500);
        }

        $hasilTest = $this->normalizeHasilTestValue($validated['hasil_test']);

        $jadwal = DB::table('jadwal_test_zoom as jtz')
            ->where('jtz.id', $id)
            ->whereNull('jtz.deleted_at')
            ->first();

        if (!$jadwal) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal test Zoom tidak ditemukan.',
            ], 404);
        }

        $daftarHadir = DB::table('daftar_hadir_test_zoom')
            ->where('jadwal_test_zoom_id', $jadwal->id)
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->first();

        $kehadiran = $this->normalizeKehadiranValue($daftarHadir->status_kehadiran ?? null);

        if (!$daftarHadir || $kehadiran !== 'hadir') {
            return response()->json([
                'success' => false,
                'message' => 'Hasil test hanya bisa diisi untuk peserta yang status kehadirannya Hadir.',
            ], 422);
        }

        DB::table('daftar_hadir_test_zoom')
            ->where('id', $daftarHadir->id)
            ->update([
                'hasil_test' => $hasilTest,
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Hasil test berhasil diperbarui.',
            'data' => [
                'jadwal_test_zoom_id' => $jadwal->id,
                'daftar_hadir_test_zoom_id' => $daftarHadir->id,
                'status_kehadiran' => $kehadiran,
                'hasil_test' => $hasilTest,
            ],
        ]);
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

    private function normalizeKehadiranValue($value): ?string
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

    private function normalizeHasilTestValue($value): ?string
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
