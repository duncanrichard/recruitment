<?php

namespace App\Http\Controllers\Admin\JadwalTest;

use App\Http\Controllers\Controller;
use App\Models\JadwalTestMmpi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MmpiController extends Controller
{
    public function index()
    {
        return view('pages.admin.index', [
            'title' => 'Jadwal Test MMPI',
        ]);
    }

    public function list(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
        ]);

        $pelamarColumns = $this->getPelamarColumns();

        $query = DB::table('jadwal_test_mmpi as jtm')
            ->join('daftar_hadir_test_zoom as dh', function ($join) {
                $join->on('dh.id', '=', 'jtm.daftar_hadir_test_zoom_id')
                    ->whereNull('dh.deleted_at');
            })
            ->join('jadwal_test_zoom as jtz', function ($join) {
                $join->on('jtz.id', '=', 'dh.jadwal_test_zoom_id')
                    ->whereNull('jtz.deleted_at');
            })
            ->join('data_riwayat_diri as drd', 'drd.id', '=', 'jtm.data_riwayat_diri_id')
            ->whereNull('jtm.deleted_at');

        if (Schema::hasColumn('data_riwayat_diri', 'deleted_at')) {
            $query->whereNull('drd.deleted_at');
        }

        if (!empty($validated['tanggal_mulai'])) {
            $query->whereDate('jtm.tanggal', '>=', $validated['tanggal_mulai']);
        }

        if (!empty($validated['tanggal_selesai'])) {
            $query->whereDate('jtm.tanggal', '<=', $validated['tanggal_selesai']);
        }

        $items = $query
            ->select([
                'jtm.id',
                'jtm.daftar_hadir_test_zoom_id',
                'jtm.data_riwayat_diri_id',
                'jtm.tanggal',
                'jtm.created_at',

                'dh.status_kehadiran',
                'dh.hasil_test',

                'jtz.id as jadwal_test_zoom_id',
                'jtz.jadwal as jadwal_zoom',
                'jtz.jadwal_mulai as jadwal_zoom_mulai',
                'jtz.jadwal_selesai as jadwal_zoom_selesai',

                DB::raw($pelamarColumns['nama'] . ' as nama'),
                DB::raw($pelamarColumns['email'] . ' as email'),
                DB::raw($pelamarColumns['no_hp'] . ' as no_hp'),
            ])
            ->orderByDesc('jtm.tanggal')
            ->orderByDesc('jtm.created_at')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'daftar_hadir_test_zoom_id' => $item->daftar_hadir_test_zoom_id,
                    'jadwal_test_zoom_id' => $item->jadwal_test_zoom_id,
                    'data_riwayat_diri_id' => $item->data_riwayat_diri_id,

                    'tanggal' => $item->tanggal
                        ? Carbon::parse($item->tanggal)->format('Y-m-d')
                        : null,

                    'jadwal_zoom' => $item->jadwal_zoom
                        ? Carbon::parse($item->jadwal_zoom)->format('Y-m-d H:i:s')
                        : null,

                    'jadwal_zoom_mulai' => $item->jadwal_zoom_mulai
                        ? Carbon::parse($item->jadwal_zoom_mulai)->format('Y-m-d H:i:s')
                        : null,

                    'jadwal_zoom_selesai' => $item->jadwal_zoom_selesai
                        ? Carbon::parse($item->jadwal_zoom_selesai)->format('Y-m-d H:i:s')
                        : null,

                    'status_kehadiran' => $this->normalizeKehadiranValue($item->status_kehadiran),
                    'hasil_test' => $this->normalizeHasilTestValue($item->hasil_test),

                    'nama' => $item->nama ?: '-',
                    'email' => $item->email ?: '-',
                    'no_hp' => $item->no_hp ?: '-',
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Data jadwal test MMPI berhasil diambil.',
            'data' => $items,
        ]);
    }

    public function kandidatLolosZoom(): JsonResponse
    {
        $pelamarColumns = $this->getPelamarColumns();

        $query = DB::table('daftar_hadir_test_zoom as dh')
            ->join('jadwal_test_zoom as jtz', function ($join) {
                $join->on('jtz.id', '=', 'dh.jadwal_test_zoom_id')
                    ->whereNull('jtz.deleted_at');
            })
            ->join('data_riwayat_diri as drd', 'drd.id', '=', 'dh.data_riwayat_diri_id')
            ->whereNull('dh.deleted_at')
            ->whereRaw("LOWER(TRIM(COALESCE(dh.status_kehadiran, ''))) = 'hadir'")
            ->whereRaw("LOWER(TRIM(COALESCE(dh.hasil_test, ''))) = 'lolos'")
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('jadwal_test_mmpi as jtm')
                    ->whereColumn('jtm.daftar_hadir_test_zoom_id', 'dh.id')
                    ->whereNull('jtm.deleted_at');
            });

        if (Schema::hasColumn('data_riwayat_diri', 'deleted_at')) {
            $query->whereNull('drd.deleted_at');
        }

        $items = $query
            ->select([
                'dh.id as daftar_hadir_test_zoom_id',
                'dh.data_riwayat_diri_id',
                'dh.tanggal_kehadiran',
                'dh.status_kehadiran',
                'dh.hasil_test',

                'jtz.id as jadwal_test_zoom_id',
                'jtz.jadwal as jadwal_zoom',
                'jtz.jadwal_mulai as jadwal_zoom_mulai',
                'jtz.jadwal_selesai as jadwal_zoom_selesai',

                DB::raw($pelamarColumns['nama'] . ' as nama'),
                DB::raw($pelamarColumns['email'] . ' as email'),
                DB::raw($pelamarColumns['no_hp'] . ' as no_hp'),
            ])
            ->orderBy($pelamarColumns['nama_order'])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->daftar_hadir_test_zoom_id,
                    'daftar_hadir_test_zoom_id' => $item->daftar_hadir_test_zoom_id,
                    'jadwal_test_zoom_id' => $item->jadwal_test_zoom_id,
                    'data_riwayat_diri_id' => $item->data_riwayat_diri_id,

                    'tanggal_kehadiran' => $item->tanggal_kehadiran,
                    'status_kehadiran' => $this->normalizeKehadiranValue($item->status_kehadiran),
                    'hasil_test' => $this->normalizeHasilTestValue($item->hasil_test),

                    'jadwal_zoom' => $item->jadwal_zoom
                        ? Carbon::parse($item->jadwal_zoom)->format('Y-m-d H:i:s')
                        : null,

                    'jadwal_zoom_mulai' => $item->jadwal_zoom_mulai
                        ? Carbon::parse($item->jadwal_zoom_mulai)->format('Y-m-d H:i:s')
                        : null,

                    'jadwal_zoom_selesai' => $item->jadwal_zoom_selesai
                        ? Carbon::parse($item->jadwal_zoom_selesai)->format('Y-m-d H:i:s')
                        : null,

                    'nama' => $item->nama ?: '-',
                    'email' => $item->email ?: '-',
                    'no_hp' => $item->no_hp ?: '-',
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Kandidat lolos Zoom berhasil diambil.',
            'data' => $items,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],

            'items' => ['required', 'array', 'min:1'],

            'items.*.daftar_hadir_test_zoom_id' => [
                'required',
                'uuid',
                Rule::exists('daftar_hadir_test_zoom', 'id')
                    ->where(fn ($query) => $query->whereNull('deleted_at')),
            ],

            'items.*.data_riwayat_diri_id' => [
                'required',
                'uuid',
                Rule::exists('data_riwayat_diri', 'id'),
            ],
        ], [
            'tanggal.required' => 'Tanggal test MMPI wajib diisi.',
            'tanggal.date' => 'Format tanggal test MMPI tidak valid.',

            'items.required' => 'Pilih minimal satu kandidat.',
            'items.array' => 'Format data kandidat tidak valid.',
            'items.min' => 'Pilih minimal satu kandidat.',

            'items.*.daftar_hadir_test_zoom_id.required' => 'Data daftar hadir Zoom wajib diisi.',
            'items.*.daftar_hadir_test_zoom_id.uuid' => 'Data daftar hadir Zoom tidak valid.',
            'items.*.daftar_hadir_test_zoom_id.exists' => 'Data daftar hadir Zoom tidak ditemukan atau sudah dihapus.',

            'items.*.data_riwayat_diri_id.required' => 'Data kandidat wajib diisi.',
            'items.*.data_riwayat_diri_id.uuid' => 'Data kandidat tidak valid.',
            'items.*.data_riwayat_diri_id.exists' => 'Data kandidat tidak ditemukan.',
        ]);

        $tanggal = Carbon::parse($validated['tanggal'])->toDateString();

        $items = collect($validated['items'])
            ->unique('daftar_hadir_test_zoom_id')
            ->values();

        $created = [];
        $skipped = [];

        DB::transaction(function () use ($items, $tanggal, &$created, &$skipped) {
            foreach ($items as $item) {
                $daftarHadir = DB::table('daftar_hadir_test_zoom as dh')
                    ->join('jadwal_test_zoom as jtz', function ($join) {
                        $join->on('jtz.id', '=', 'dh.jadwal_test_zoom_id')
                            ->whereNull('jtz.deleted_at');
                    })
                    ->where('dh.id', $item['daftar_hadir_test_zoom_id'])
                    ->whereNull('dh.deleted_at')
                    ->select([
                        'dh.id',
                        'dh.data_riwayat_diri_id',
                        'dh.status_kehadiran',
                        'dh.hasil_test',
                        'dh.jadwal_test_zoom_id',
                    ])
                    ->first();

                if (!$daftarHadir) {
                    $skipped[] = [
                        'daftar_hadir_test_zoom_id' => $item['daftar_hadir_test_zoom_id'],
                        'reason' => 'Data daftar hadir Zoom atau jadwal Zoom sudah dihapus.',
                    ];
                    continue;
                }

                $statusKehadiran = $this->normalizeKehadiranValue($daftarHadir->status_kehadiran ?? null);
                $hasilTest = $this->normalizeHasilTestValue($daftarHadir->hasil_test ?? null);

                if ($statusKehadiran !== 'hadir' || $hasilTest !== 'lolos') {
                    $skipped[] = [
                        'daftar_hadir_test_zoom_id' => $item['daftar_hadir_test_zoom_id'],
                        'reason' => 'Kandidat belum hadir dan lolos test Zoom.',
                    ];
                    continue;
                }

                if ((string) $daftarHadir->data_riwayat_diri_id !== (string) $item['data_riwayat_diri_id']) {
                    $skipped[] = [
                        'daftar_hadir_test_zoom_id' => $item['daftar_hadir_test_zoom_id'],
                        'reason' => 'Data kandidat tidak sesuai dengan daftar hadir Zoom.',
                    ];
                    continue;
                }

                $exists = JadwalTestMmpi::query()
                    ->where('daftar_hadir_test_zoom_id', $item['daftar_hadir_test_zoom_id'])
                    ->whereNull('deleted_at')
                    ->exists();

                if ($exists) {
                    $skipped[] = [
                        'daftar_hadir_test_zoom_id' => $item['daftar_hadir_test_zoom_id'],
                        'reason' => 'Kandidat sudah mendapatkan jadwal test MMPI.',
                    ];
                    continue;
                }

                $created[] = JadwalTestMmpi::query()->create([
                    'id' => (string) Str::uuid(),
                    'daftar_hadir_test_zoom_id' => $daftarHadir->id,
                    'data_riwayat_diri_id' => $daftarHadir->data_riwayat_diri_id,
                    'tanggal' => $tanggal,
                ]);
            }
        });

        return response()->json([
            'success' => count($created) > 0,
            'message' => count($created) > 0
                ? count($created) . ' jadwal test MMPI berhasil dibuat.'
                : 'Tidak ada jadwal test MMPI yang dibuat. Kandidat mungkin sudah mendapatkan jadwal MMPI atau data Zoom sudah dihapus.',
            'created_count' => count($created),
            'skipped_count' => count($skipped),
            'skipped' => $skipped,
        ], count($created) > 0 ? 201 : 422);
    }

    public function destroy(string $id): JsonResponse
    {
        $jadwal = JadwalTestMmpi::query()->findOrFail($id);

        DB::transaction(function () use ($jadwal) {
            if (Schema::hasTable('daftar_hadir_test_mmpi')) {
                DB::table('daftar_hadir_test_mmpi')
                    ->where('jadwal_test_mmpi_id', $jadwal->id)
                    ->whereNull('deleted_at')
                    ->update([
                        'deleted_at' => now(),
                        'updated_at' => now(),
                    ]);
            }

            $jadwal->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Jadwal test MMPI berhasil dihapus.',
        ]);
    }

    private function getPelamarColumns(): array
    {
        $namaColumn = $this->firstExistingColumn('data_riwayat_diri', [
            'nama_lengkap',
            'nama',
            'nama_pelamar',
        ]);

        $emailColumn = $this->firstExistingColumn('data_riwayat_diri', [
            'email',
        ]);

        $noHpColumn = $this->firstExistingColumn('data_riwayat_diri', [
            'no_wa',
            'no_hp',
            'nomor_hp',
            'no_telepon',
            'telepon',
        ]);

        return [
            'nama' => $namaColumn ? "drd.{$namaColumn}" : "'-'",
            'nama_order' => $namaColumn ? "drd.{$namaColumn}" : "drd.id",
            'email' => $emailColumn ? "drd.{$emailColumn}" : "'-'",
            'no_hp' => $noHpColumn ? "drd.{$noHpColumn}" : "'-'",
        ];
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
}