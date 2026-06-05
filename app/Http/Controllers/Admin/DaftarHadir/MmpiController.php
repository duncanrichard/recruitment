<?php

namespace App\Http\Controllers\Admin\DaftarHadir;

use App\Http\Controllers\Controller;
use App\Models\DaftarHadirTestMmpi;
use App\Models\JadwalTestMmpi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MmpiController extends Controller
{
    public function groups(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'search' => ['nullable', 'string', 'max:150'],
        ]);

        $query = DB::table('jadwal_test_mmpi as jmm')
            ->leftJoin('daftar_hadir_test_mmpi as dhm', function ($join) {
                $join->on('dhm.jadwal_test_mmpi_id', '=', 'jmm.id');

                if (Schema::hasColumn('daftar_hadir_test_mmpi', 'deleted_at')) {
                    $join->whereNull('dhm.deleted_at');
                }
            })
            ->whereNotNull('jmm.tanggal');

        if (Schema::hasColumn('jadwal_test_mmpi', 'deleted_at')) {
            $query->whereNull('jmm.deleted_at');
        }

        if (!empty($validated['tanggal_mulai'])) {
            $query->whereDate('jmm.tanggal', '>=', $validated['tanggal_mulai']);
        }

        if (!empty($validated['tanggal_selesai'])) {
            $query->whereDate('jmm.tanggal', '<=', $validated['tanggal_selesai']);
        }

        if (!empty($validated['search'])) {
            $keyword = trim($validated['search']);
            $query->whereRaw('CAST(DATE(jmm.tanggal) AS TEXT) ILIKE ?', ["%{$keyword}%"]);
        }

        $data = $query
            ->selectRaw('DATE(jmm.tanggal) as tanggal_test')
            ->selectRaw('COUNT(DISTINCT jmm.id) as total_peserta')
            ->selectRaw("COUNT(DISTINCT CASE WHEN LOWER(COALESCE(dhm.status_kehadiran, '')) = 'hadir' THEN jmm.id END) as total_hadir")
            ->selectRaw("COUNT(DISTINCT CASE WHEN LOWER(REPLACE(REPLACE(COALESCE(dhm.status_kehadiran, ''), ' ', '_'), '-', '_')) IN ('tidak_hadir', 'tidakhadir', 'tidak') THEN jmm.id END) as total_tidak_hadir")
            ->selectRaw("COUNT(DISTINCT CASE WHEN dhm.id IS NULL OR TRIM(COALESCE(dhm.status_kehadiran, '')) = '' THEN jmm.id END) as total_belum_ada")
            ->selectRaw("COUNT(DISTINCT CASE WHEN LOWER(COALESCE(dhm.hasil_test, '')) = 'lolos' THEN jmm.id END) as total_lolos")
            ->selectRaw("COUNT(DISTINCT CASE WHEN LOWER(REPLACE(REPLACE(COALESCE(dhm.hasil_test, ''), ' ', '_'), '-', '_')) IN ('gagal', 'tidak_lolos', 'tidaklolos') THEN jmm.id END) as total_gagal")
            ->groupBy(DB::raw('DATE(jmm.tanggal)'))
            ->orderByDesc(DB::raw('DATE(jmm.tanggal)'))
            ->get()
            ->map(function ($row) {
                return [
                    'tanggal_test' => $row->tanggal_test,
                    'tanggal_label' => $this->formatTanggalIndonesia($row->tanggal_test),
                    'total_peserta' => (int) $row->total_peserta,
                    'total_hadir' => (int) $row->total_hadir,
                    'total_tidak_hadir' => (int) $row->total_tidak_hadir,
                    'total_belum_ada' => (int) $row->total_belum_ada,
                    'total_lolos' => (int) $row->total_lolos,
                    'total_gagal' => (int) $row->total_gagal,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Group daftar hadir test MMPI berhasil diambil.',
            'summary' => [
                'total' => $data->sum('total_peserta'),
                'hadir' => $data->sum('total_hadir'),
                'tidak_hadir' => $data->sum('total_tidak_hadir'),
                'belum_ada' => $data->sum('total_belum_ada'),
                'lolos' => $data->sum('total_lolos'),
                'gagal' => $data->sum('total_gagal'),
            ],
            'data' => $data->values(),
        ]);
    }

    public function list(Request $request): JsonResponse
    {
        if ($request->filled('tanggal')) {
            return $this->detail($request);
        }

        return $this->groups($request);
    }

    public function detail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'status' => ['nullable', Rule::in(['semua', 'hadir', 'tidak_hadir', 'lolos', 'gagal'])],
            'search' => ['nullable', 'string', 'max:150'],
        ]);

        $tanggal = $validated['tanggal'];
        $status = $validated['status'] ?? 'semua';
        $pelamarColumns = $this->getPelamarColumns();

        $query = DB::table('jadwal_test_mmpi as jmm')
            ->join('data_riwayat_diri as drd', 'drd.id', '=', 'jmm.data_riwayat_diri_id')
            ->leftJoin('daftar_hadir_test_mmpi as dhm', function ($join) {
                $join->on('dhm.jadwal_test_mmpi_id', '=', 'jmm.id');

                if (Schema::hasColumn('daftar_hadir_test_mmpi', 'deleted_at')) {
                    $join->whereNull('dhm.deleted_at');
                }
            })
            ->whereDate('jmm.tanggal', $tanggal);

        if (Schema::hasColumn('jadwal_test_mmpi', 'deleted_at')) {
            $query->whereNull('jmm.deleted_at');
        }

        if (Schema::hasColumn('data_riwayat_diri', 'deleted_at')) {
            $query->whereNull('drd.deleted_at');
        }

        if ($status === 'hadir') {
            $query->whereRaw("LOWER(COALESCE(dhm.status_kehadiran, '')) = 'hadir'");
        }

        if ($status === 'tidak_hadir') {
            $query->whereRaw("LOWER(REPLACE(REPLACE(COALESCE(dhm.status_kehadiran, ''), ' ', '_'), '-', '_')) IN ('tidak_hadir', 'tidakhadir', 'tidak')");
        }

        if ($status === 'lolos') {
            $query->whereRaw("LOWER(COALESCE(dhm.hasil_test, '')) = 'lolos'");
        }

        if ($status === 'gagal') {
            $query->whereRaw("LOWER(REPLACE(REPLACE(COALESCE(dhm.hasil_test, ''), ' ', '_'), '-', '_')) IN ('gagal', 'tidak_lolos', 'tidaklolos')");
        }

        if (!empty($validated['search'])) {
            $keyword = trim($validated['search']);
            $query->where(function ($q) use ($keyword, $pelamarColumns) {
                $q->whereRaw($pelamarColumns['nama'] . ' ILIKE ?', ["%{$keyword}%"])
                    ->orWhereRaw($pelamarColumns['email'] . ' ILIKE ?', ["%{$keyword}%"])
                    ->orWhereRaw($pelamarColumns['no_hp'] . ' ILIKE ?', ["%{$keyword}%"]);
            });
        }

        $selects = [
            'jmm.id as jadwal_test_mmpi_id',
            'jmm.data_riwayat_diri_id',
            'jmm.tanggal as tanggal_mmpi',
            'dhm.id as daftar_hadir_test_mmpi_id',
            'dhm.tanggal_kehadiran',
            'dhm.status_kehadiran',
            'dhm.hasil_test',
            DB::raw($pelamarColumns['nama'] . ' as nama'),
            DB::raw($pelamarColumns['email'] . ' as email'),
            DB::raw($pelamarColumns['no_hp'] . ' as no_hp'),
        ];

        if (Schema::hasColumn('daftar_hadir_test_mmpi', 'file_hasil_test_mmpi')) {
            $selects[] = 'dhm.file_hasil_test_mmpi';
        } else {
            $selects[] = DB::raw("NULL as file_hasil_test_mmpi");
        }

        $items = $query
            ->select($selects)
            ->orderBy('jmm.tanggal')
            ->orderBy($pelamarColumns['nama_order'])
            ->get()
            ->map(function ($item) {
                $statusKehadiran = $this->normalizeKehadiranValue($item->status_kehadiran ?? null);
                $hasilTest = $this->normalizeHasilTestValue($item->hasil_test ?? null);
                $fileUrl = $this->normalizeFileUrl($item->file_hasil_test_mmpi ?? null);

                return [
                    'id' => $item->jadwal_test_mmpi_id,
                    'jadwal_test_mmpi_id' => $item->jadwal_test_mmpi_id,
                    'daftar_hadir_test_mmpi_id' => $item->daftar_hadir_test_mmpi_id,
                    'data_riwayat_diri_id' => $item->data_riwayat_diri_id,
                    'tanggal_mmpi' => $item->tanggal_mmpi ? Carbon::parse($item->tanggal_mmpi)->format('Y-m-d H:i:s') : null,
                    'tanggal_kehadiran' => $item->tanggal_kehadiran ? Carbon::parse($item->tanggal_kehadiran)->format('Y-m-d') : null,
                    'status_kehadiran' => $statusKehadiran,
                    'status_kehadiran_label' => $this->labelKehadiran($statusKehadiran),
                    'hasil_test' => $hasilTest,
                    'hasil_test_label' => $this->labelHasilTest($hasilTest),
                    'file_hasil_test_mmpi' => $fileUrl,
                    'file_hasil_test_mmpi_url' => $fileUrl,
                    'nama' => $item->nama ?: '-',
                    'email' => $item->email ?: '-',
                    'no_hp' => $item->no_hp ?: '-',
                ];
            });

        $allItems = $this->itemsByTanggal($tanggal);

        return response()->json([
            'success' => true,
            'message' => 'Detail daftar hadir test MMPI berhasil diambil.',
            'tanggal' => $tanggal,
            'tanggal_label' => $this->formatTanggalIndonesia($tanggal),
            'summary' => [
                'total' => $allItems->count(),
                'hadir' => $allItems->where('status_kehadiran', 'hadir')->count(),
                'tidak_hadir' => $allItems->where('status_kehadiran', 'tidak_hadir')->count(),
                'belum_ada' => $allItems->where('status_kehadiran', null)->count(),
                'lolos' => $allItems->where('hasil_test', 'lolos')->count(),
                'gagal' => $allItems->where('hasil_test', 'gagal')->count(),
            ],
            'data' => $items->values(),
        ]);
    }

    public function updateHasilTest(Request $request, string $jadwalTestMmpi): JsonResponse
    {
        $validated = $request->validate([
            'hasil_test' => ['required', Rule::in(['lolos', 'gagal'])],
            'file_hasil_test_mmpi' => [
                'nullable',
                'file',
                'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
                'max:5120',
            ],
        ], [
            'hasil_test.required' => 'Hasil test wajib dipilih.',
            'hasil_test.in' => 'Hasil test tidak valid.',
            'file_hasil_test_mmpi.file' => 'File hasil test MMPI tidak valid.',
            'file_hasil_test_mmpi.mimes' => 'File harus berupa PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, atau PNG.',
            'file_hasil_test_mmpi.max' => 'Ukuran file maksimal 5 MB.',
        ]);

        if (!Schema::hasColumn('daftar_hadir_test_mmpi', 'file_hasil_test_mmpi')) {
            return response()->json([
                'success' => false,
                'message' => 'Kolom file_hasil_test_mmpi belum tersedia. Jalankan migration terlebih dahulu.',
            ], 500);
        }

        $jadwal = JadwalTestMmpi::query()->findOrFail($jadwalTestMmpi);

        $daftarHadir = DaftarHadirTestMmpi::query()
            ->where('jadwal_test_mmpi_id', $jadwal->id)
            ->first();

        if (!$daftarHadir) {
            return response()->json([
                'success' => false,
                'message' => 'Status kehadiran belum diisi. Silakan isi kehadiran terlebih dahulu.',
            ], 422);
        }

        if ($this->normalizeKehadiranValue($daftarHadir->status_kehadiran) !== 'hadir') {
            return response()->json([
                'success' => false,
                'message' => 'Hasil test hanya bisa diisi untuk kandidat yang hadir.',
            ], 422);
        }

        $namaKandidat = $this->getNamaKandidatByJadwal($jadwal);
        $fileUrl = $this->normalizeFileUrl($daftarHadir->file_hasil_test_mmpi ?? null);

        if ($request->hasFile('file_hasil_test_mmpi')) {
            $oldPath = $this->convertPublicUrlToStoragePath($fileUrl);

            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }

            $folderNamaKandidat = $this->sanitizeFolderName($namaKandidat ?: 'tanpa nama');
            $extension = $request->file('file_hasil_test_mmpi')->getClientOriginalExtension();

            $fileName = 'hasil-test-mmpi-' .
                now()->format('Ymd-His') .
                '-' .
                Str::random(8) .
                '.' .
                $extension;

            $storedPath = $request->file('file_hasil_test_mmpi')->storeAs(
                "test mmpi/{$folderNamaKandidat}/dokumen",
                $fileName,
                'public'
            );

            $fileUrl = '/storage/' . $storedPath;
        }

        $daftarHadir->update([
            'hasil_test' => $validated['hasil_test'],
            'file_hasil_test_mmpi' => $fileUrl,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Hasil test MMPI berhasil diperbarui.',
            'data' => [
                'id' => $daftarHadir->id,
                'jadwal_test_mmpi_id' => $jadwal->id,
                'daftar_hadir_test_mmpi_id' => $daftarHadir->id,
                'status_kehadiran' => $this->normalizeKehadiranValue($daftarHadir->status_kehadiran),
                'hasil_test' => $this->normalizeHasilTestValue($validated['hasil_test']),
                'hasil_test_label' => $this->labelHasilTest($this->normalizeHasilTestValue($validated['hasil_test'])),
                'file_hasil_test_mmpi' => $fileUrl,
                'file_hasil_test_mmpi_url' => $fileUrl,
            ],
        ]);
    }

    public function updateKehadiran(Request $request, string $jadwalTestMmpi): JsonResponse
    {
        $validated = $request->validate([
            'status_kehadiran' => ['required', Rule::in(['hadir', 'tidak_hadir'])],
        ]);

        $jadwal = JadwalTestMmpi::query()->findOrFail($jadwalTestMmpi);

        $daftarHadir = DaftarHadirTestMmpi::withTrashed()
            ->where('jadwal_test_mmpi_id', $jadwal->id)
            ->first();

        $payload = [
            'jadwal_test_mmpi_id' => $jadwal->id,
            'data_riwayat_diri_id' => $jadwal->data_riwayat_diri_id,
            'tanggal_kehadiran' => Carbon::parse($jadwal->tanggal)->toDateString(),
            'status_kehadiran' => $validated['status_kehadiran'],
        ];

        if ($validated['status_kehadiran'] !== 'hadir') {
            $payload['hasil_test'] = null;
        }

        if ($daftarHadir) {
            $daftarHadir->restore();
            $daftarHadir->forceFill($payload)->save();
        } else {
            $daftarHadir = DaftarHadirTestMmpi::query()->create($payload);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status kehadiran MMPI berhasil diperbarui.',
            'data' => $daftarHadir,
        ]);
    }

    private function itemsByTanggal(string $tanggal)
    {
        return DB::table('jadwal_test_mmpi as jmm')
            ->leftJoin('daftar_hadir_test_mmpi as dhm', function ($join) {
                $join->on('dhm.jadwal_test_mmpi_id', '=', 'jmm.id');

                if (Schema::hasColumn('daftar_hadir_test_mmpi', 'deleted_at')) {
                    $join->whereNull('dhm.deleted_at');
                }
            })
            ->whereDate('jmm.tanggal', $tanggal)
            ->when(Schema::hasColumn('jadwal_test_mmpi', 'deleted_at'), function ($query) {
                $query->whereNull('jmm.deleted_at');
            })
            ->select(['jmm.id', 'dhm.status_kehadiran', 'dhm.hasil_test'])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'status_kehadiran' => $this->normalizeKehadiranValue($item->status_kehadiran ?? null),
                    'hasil_test' => $this->normalizeHasilTestValue($item->hasil_test ?? null),
                ];
            });
    }

    private function getPelamarColumns(): array
    {
        $namaColumn = $this->firstExistingColumn('data_riwayat_diri', [
            'nama_lengkap',
            'nama',
            'nama_pelamar',
        ]);

        $emailColumn = $this->firstExistingColumn('data_riwayat_diri', ['email']);

        $noHpColumn = $this->firstExistingColumn('data_riwayat_diri', [
            'no_wa',
            'no_hp',
            'nomor_hp',
            'no_telepon',
            'telepon',
        ]);

        return [
            'nama' => $namaColumn ? "drd.{$namaColumn}" : "'-'",
            'nama_order' => $namaColumn ? "drd.{$namaColumn}" : 'drd.id',
            'email' => $emailColumn ? "drd.{$emailColumn}" : "'-'",
            'no_hp' => $noHpColumn ? "drd.{$noHpColumn}" : "'-'",
        ];
    }

    private function getNamaKandidatByJadwal(JadwalTestMmpi $jadwal): string
    {
        $namaColumn = $this->firstExistingColumn('data_riwayat_diri', [
            'nama_lengkap',
            'nama',
            'nama_pelamar',
        ]);

        if (!$namaColumn) {
            return 'tanpa nama';
        }

        $row = DB::table('data_riwayat_diri')
            ->where('id', $jadwal->data_riwayat_diri_id)
            ->select(DB::raw("{$namaColumn} as nama"))
            ->first();

        return $row?->nama ?: 'tanpa nama';
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

    private function sanitizeFolderName(string $value): string
    {
        $value = trim($value);

        if ($value === '' || $value === '-') {
            return 'tanpa-nama';
        }

        $value = preg_replace('/[\\\\\/:*?"<>|]+/', '', $value);
        $value = preg_replace('/\s+/', ' ', $value);
        $value = trim($value);

        return $value !== '' ? $value : 'tanpa-nama';
    }

    private function normalizeFileUrl(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if (!str_starts_with($value, 'http://') &&
            !str_starts_with($value, 'https://') &&
            !str_starts_with($value, '/storage/')
        ) {
            return '/storage/' . ltrim($value, '/');
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            $path = parse_url($value, PHP_URL_PATH);

            return $path ?: $value;
        }

        return $value;
    }

    private function convertPublicUrlToStoragePath(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        $url = $this->normalizeFileUrl($url);

        if (!$url) {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH);

        if (!$path) {
            return null;
        }

        $storagePrefix = '/storage/';

        if (!str_starts_with($path, $storagePrefix)) {
            return null;
        }

        return urldecode(substr($path, strlen($storagePrefix)));
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

        if (in_array($normalized, ['lolos', '1', 'true', 'ya', 'yes'], true)) {
            return 'lolos';
        }

        if (in_array($normalized, ['gagal', 'tidak_lolos', 'tidaklolos', '0', 'false', 'tidak', 'no'], true)) {
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
            'gagal' => 'Tidak Lolos',
            default => 'Belum Ada',
        };
    }

    private function formatTanggalIndonesia(?string $value): string
    {
        if (!$value) {
            return '-';
        }

        try {
            return Carbon::parse($value)->locale('id')->translatedFormat('d F Y');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }
}
