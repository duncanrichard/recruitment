<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataRiwayatDiri;
use App\Models\JadwalTestZoom;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class JadwalTestZoomController extends Controller
{
    public function list(Request $request): JsonResponse
    {
        $request->validate([
            'tanggal_test' => ['nullable', 'date'],
            'filter_tanggal_test' => ['nullable', 'date'],
            'tanggal_test_mulai' => ['nullable', 'date'],
            'tanggal_test_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_test_mulai'],
        ], [
            'tanggal_test.date' => 'Tanggal test tidak valid.',
            'filter_tanggal_test.date' => 'Tanggal test tidak valid.',
            'tanggal_test_mulai.date' => 'Tanggal test mulai tidak valid.',
            'tanggal_test_selesai.date' => 'Tanggal test selesai tidak valid.',
            'tanggal_test_selesai.after_or_equal' => 'Tanggal test selesai harus sama atau setelah tanggal test mulai.',
        ]);

        $query = JadwalTestZoom::query()
            ->with([
                'dataRiwayatDiri:id,nama_lengkap,nama_panggil,email,no_wa,token,tanggal_skrining,posisi_yang_dilamar,perusahaan_dilamar',
                'dataRiwayatDiri.posisi:id,nama_posisi',
                'dataRiwayatDiri.perusahaan:id,nama_perusahaan',
            ])
            ->latest('jadwal_mulai')
            ->latest('jadwal');

        $this->applyTanggalTestFilter($query, $request);

        $rows = $query->get();

        $data = $rows
            ->groupBy(function ($item) {
                return $this->makeGroupKeyFromItem($item);
            })
            ->map(function ($items, $groupKey) {
                $first = $items->first();
                $jadwalMulai = $this->getJadwalMulai($first);
                $jadwalSelesai = $this->getJadwalSelesai($first);
                $sesi = $this->getSesi($first);

                return [
                    'id' => $groupKey,
                    'group_key' => $groupKey,

                    'sesi' => $sesi,
                    'sesi_label' => $sesi,

                    'tanggal_test' => $jadwalMulai?->toDateString(),
                    'tanggal_test_label' => $jadwalMulai
                        ? $jadwalMulai->translatedFormat('d F Y')
                        : '-',

                    'jam_test' => $this->formatJamRange($jadwalMulai, $jadwalSelesai),

                    'jadwal' => $jadwalMulai?->toDateTimeString(),
                    'jadwal_mulai' => $jadwalMulai?->toDateTimeString(),
                    'jadwal_selesai' => $jadwalSelesai?->toDateTimeString(),

                    'jadwal_label' => $this->formatJadwalLabel($jadwalMulai, $jadwalSelesai, $sesi),

                    'link_zoom' => $first?->link_zoom,
                    'link_zoom_label' => $first?->link_zoom ?: '-',

                    'total_pelamar' => $items->count(),

                    'total_hadir' => $items
                        ->filter(fn ($item) => $this->normalizeKehadiran($item->kehadiran ?? null) === 'hadir')
                        ->count(),

                    'total_tidak_hadir' => $items
                        ->filter(fn ($item) => $this->normalizeKehadiran($item->kehadiran ?? null) === 'tidak_hadir')
                        ->count(),

                    'total_belum_konfirmasi' => $items
                        ->filter(fn ($item) => !$this->normalizeKehadiran($item->kehadiran ?? null))
                        ->count(),

                    'pelamar_ids' => $items
                        ->pluck('data_riwayat_diri_id')
                        ->filter()
                        ->values()
                        ->toArray(),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function detail(string $groupKey): JsonResponse
    {
        $rows = $this->rowsByGroupKey($groupKey);

        if ($rows->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Data jadwal tidak ditemukan.',
            ], 404);
        }

        $rows = $rows
            ->sortBy(function ($item) {
                return strtolower($item->dataRiwayatDiri->nama_lengkap ?? '');
            })
            ->values();

        $first = $rows->first();

        return response()->json([
            'success' => true,
            'data' => $this->formatDetailGroup(
                $rows,
                $this->getJadwalMulai($first),
                $this->getJadwalSelesai($first),
                $this->getSesi($first),
                $groupKey
            ),
        ]);
    }

    public function detailByTanggal(string $tanggal): JsonResponse
    {
        $tanggalCarbon = $this->parseDate($tanggal);

        if (!$tanggalCarbon) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal test tidak valid.',
            ], 422);
        }

        $rows = $this->jadwalDetailQuery()
            ->whereDate('jadwal_mulai', $tanggalCarbon->toDateString())
            ->orWhere(function ($query) use ($tanggalCarbon) {
                $query
                    ->whereNull('jadwal_mulai')
                    ->whereDate('jadwal', $tanggalCarbon->toDateString());
            })
            ->latest('jadwal_mulai')
            ->latest('jadwal')
            ->get()
            ->sortBy(function ($item) {
                return strtolower($item->dataRiwayatDiri->nama_lengkap ?? '');
            })
            ->values();

        $data = $rows
            ->groupBy(function ($item) {
                return $this->makeGroupKeyFromItem($item);
            })
            ->map(function ($items, $groupKey) {
                $first = $items->first();

                return $this->formatDetailGroup(
                    $items,
                    $this->getJadwalMulai($first),
                    $this->getJadwalSelesai($first),
                    $this->getSesi($first),
                    $groupKey
                );
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function pelamarList(): JsonResponse
    {
        $sudahDijadwalkanIds = JadwalTestZoom::query()
            ->pluck('data_riwayat_diri_id')
            ->filter()
            ->values()
            ->toArray();

        $data = DataRiwayatDiri::query()
            ->with([
                'posisi:id,nama_posisi',
                'perusahaan:id,nama_perusahaan',
            ])
            ->whereNotNull('tanggal_skrining')
            ->orderByDesc('tanggal_skrining')
            ->orderBy('nama_lengkap')
            ->get([
                'id',
                'nama_lengkap',
                'nama_panggil',
                'email',
                'no_wa',
                'token',
                'tanggal_skrining',
                'posisi_yang_dilamar',
                'perusahaan_dilamar',
            ])
            ->map(function ($item) use ($sudahDijadwalkanIds) {
                $item->sudah_dijadwalkan = in_array(
                    $item->id,
                    $sudahDijadwalkanIds,
                    true
                );

                return $item;
            });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->mergeScheduleInputs($request);

        $validated = $request->validate([
            'tanggal_skrining' => ['required', 'date'],

            'data_riwayat_diri_ids' => ['required', 'array', 'min:1'],
            'data_riwayat_diri_ids.*' => [
                'required',
                'uuid',
                Rule::exists('data_riwayat_diri', 'id'),
            ],

            'sesi' => ['required', 'string', 'max:100'],
            'jadwal_mulai' => ['required', 'date'],
            'jadwal_selesai' => ['required', 'date', 'after:jadwal_mulai'],

            'link_zoom' => ['nullable', 'url', 'max:2048'],
        ], $this->validationMessages());

        $jadwalMulai = Carbon::parse($validated['jadwal_mulai']);
        $jadwalSelesai = Carbon::parse($validated['jadwal_selesai']);

        if ($jadwalMulai->lessThanOrEqualTo(now())) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal mulai Zoom tidak boleh menggunakan jam yang sudah lewat.',
            ], 422);
        }

        $tanggalSkrining = Carbon::parse($validated['tanggal_skrining'])->toDateString();

        $pelamarIdsRequest = collect($validated['data_riwayat_diri_ids'])
            ->unique()
            ->values()
            ->toArray();

        $pelamarIdsSesuaiTanggal = DataRiwayatDiri::query()
            ->whereDate('tanggal_skrining', $tanggalSkrining)
            ->whereIn('id', $pelamarIdsRequest)
            ->pluck('id')
            ->toArray();

        if (count($pelamarIdsSesuaiTanggal) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada pelamar yang sesuai dengan tanggal skrining yang dipilih.',
            ], 422);
        }

        $pelamarSudahDijadwalkan = JadwalTestZoom::query()
            ->whereIn('data_riwayat_diri_id', $pelamarIdsSesuaiTanggal)
            ->pluck('data_riwayat_diri_id')
            ->toArray();

        $pelamarBelumDijadwalkan = array_values(array_diff(
            $pelamarIdsSesuaiTanggal,
            $pelamarSudahDijadwalkan
        ));

        if (count($pelamarBelumDijadwalkan) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Semua pelamar pada tanggal skrining tersebut sudah memiliki jadwal Zoom.',
            ], 422);
        }

        $groupKey = $this->makeGroupKey($validated['sesi'], $jadwalMulai, $jadwalSelesai);

        $created = DB::transaction(function () use (
            $pelamarBelumDijadwalkan,
            $validated,
            $jadwalMulai,
            $jadwalSelesai,
            $groupKey
        ) {
            $items = [];

            foreach ($pelamarBelumDijadwalkan as $pelamarId) {
                $items[] = JadwalTestZoom::query()->create([
                    'data_riwayat_diri_id' => $pelamarId,
                    'sesi' => $validated['sesi'],
                    'group_key' => $groupKey,
                    'jadwal' => $jadwalMulai->toDateTimeString(),
                    'jadwal_mulai' => $jadwalMulai->toDateTimeString(),
                    'jadwal_selesai' => $jadwalSelesai->toDateTimeString(),
                    'link_zoom' => $validated['link_zoom'] ?? null,
                ]);
            }

            return $items;
        });

        $freshData = JadwalTestZoom::query()
            ->with([
                'dataRiwayatDiri',
                'dataRiwayatDiri.posisi',
                'dataRiwayatDiri.perusahaan',
            ])
            ->whereIn('id', collect($created)->pluck('id')->toArray())
            ->latest('jadwal_mulai')
            ->get();

        $jumlahSkip = count($pelamarSudahDijadwalkan);

        return response()->json([
            'success' => true,
            'message' => count($created) . ' jadwal test Zoom berhasil disimpan.'
                . ($jumlahSkip > 0 ? ' ' . $jumlahSkip . ' pelamar dilewati karena sudah dijadwalkan.' : ''),
            'data' => $freshData,
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $this->mergeScheduleInputs($request);

        $data = JadwalTestZoom::query()->findOrFail($id);

        $validated = $request->validate([
            'data_riwayat_diri_id' => [
                'required',
                'uuid',
                Rule::exists('data_riwayat_diri', 'id'),
            ],

            'sesi' => ['required', 'string', 'max:100'],
            'jadwal_mulai' => ['required', 'date'],
            'jadwal_selesai' => ['required', 'date', 'after:jadwal_mulai'],

            'link_zoom' => ['nullable', 'url', 'max:2048'],
        ], $this->validationMessages());

        $jadwalMulai = Carbon::parse($validated['jadwal_mulai']);
        $jadwalSelesai = Carbon::parse($validated['jadwal_selesai']);

        if ($jadwalMulai->lessThanOrEqualTo(now())) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal mulai Zoom tidak boleh menggunakan jam yang sudah lewat.',
            ], 422);
        }

        $pelamarSudahPunyaJadwal = JadwalTestZoom::query()
            ->where('data_riwayat_diri_id', $validated['data_riwayat_diri_id'])
            ->where('id', '!=', $data->id)
            ->exists();

        if ($pelamarSudahPunyaJadwal) {
            return response()->json([
                'success' => false,
                'message' => 'Pelamar ini sudah memiliki jadwal Zoom.',
            ], 422);
        }

        $oldGroupKey = $this->makeGroupKeyFromItem($data);
        $newGroupKey = $this->makeGroupKey($validated['sesi'], $jadwalMulai, $jadwalSelesai);
        $newLinkZoom = $validated['link_zoom'] ?? null;

        DB::transaction(function () use (
            $data,
            $validated,
            $oldGroupKey,
            $newGroupKey,
            $jadwalMulai,
            $jadwalSelesai,
            $newLinkZoom
        ) {
            $data->forceFill([
                'data_riwayat_diri_id' => $validated['data_riwayat_diri_id'],
                'sesi' => $validated['sesi'],
                'group_key' => $newGroupKey,
                'jadwal' => $jadwalMulai->toDateTimeString(),
                'jadwal_mulai' => $jadwalMulai->toDateTimeString(),
                'jadwal_selesai' => $jadwalSelesai->toDateTimeString(),
                'link_zoom' => $newLinkZoom,
            ])->save();

            JadwalTestZoom::query()
                ->where('id', '!=', $data->id)
                ->where('group_key', $oldGroupKey)
                ->update([
                    'sesi' => $validated['sesi'],
                    'group_key' => $newGroupKey,
                    'jadwal' => $jadwalMulai->toDateTimeString(),
                    'jadwal_mulai' => $jadwalMulai->toDateTimeString(),
                    'jadwal_selesai' => $jadwalSelesai->toDateTimeString(),
                    'link_zoom' => $newLinkZoom,
                ]);
        });

        $freshData = $data->fresh([
            'dataRiwayatDiri',
            'dataRiwayatDiri.posisi',
            'dataRiwayatDiri.perusahaan',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal test Zoom berhasil diperbarui.',
            'data' => $freshData,
        ]);
    }

    public function updateGroup(Request $request, string $groupKey): JsonResponse
    {
        $this->mergeScheduleInputs($request);

        $rows = $this->rowsByGroupKey($groupKey);

        if ($rows->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Data jadwal tidak ditemukan.',
            ], 404);
        }

        $validated = $request->validate([
            'sesi' => ['required', 'string', 'max:100'],
            'jadwal_mulai' => ['required', 'date'],
            'jadwal_selesai' => ['required', 'date', 'after:jadwal_mulai'],
            'link_zoom' => ['nullable', 'url', 'max:2048'],
        ], $this->validationMessages());

        $jadwalMulai = Carbon::parse($validated['jadwal_mulai']);
        $jadwalSelesai = Carbon::parse($validated['jadwal_selesai']);

        if ($jadwalMulai->lessThanOrEqualTo(now())) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal mulai Zoom tidak boleh menggunakan jam yang sudah lewat.',
            ], 422);
        }

        $newGroupKey = $this->makeGroupKey($validated['sesi'], $jadwalMulai, $jadwalSelesai);
        $newLinkZoom = $validated['link_zoom'] ?? null;

        DB::transaction(function () use (
            $groupKey,
            $validated,
            $jadwalMulai,
            $jadwalSelesai,
            $newGroupKey,
            $newLinkZoom
        ) {
            JadwalTestZoom::query()
                ->where('group_key', $groupKey)
                ->update([
                    'sesi' => $validated['sesi'],
                    'group_key' => $newGroupKey,
                    'jadwal' => $jadwalMulai->toDateTimeString(),
                    'jadwal_mulai' => $jadwalMulai->toDateTimeString(),
                    'jadwal_selesai' => $jadwalSelesai->toDateTimeString(),
                    'link_zoom' => $newLinkZoom,
                ]);
        });

        $freshRows = $this->jadwalDetailQuery()
            ->where('group_key', $newGroupKey)
            ->get()
            ->sortBy(function ($item) {
                return strtolower($item->dataRiwayatDiri->nama_lengkap ?? '');
            })
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Group jadwal test Zoom berhasil diperbarui.',
            'data' => $this->formatDetailGroup(
                $freshRows,
                $jadwalMulai,
                $jadwalSelesai,
                $validated['sesi'],
                $newGroupKey
            ),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $data = JadwalTestZoom::query()->findOrFail($id);

        try {
            $data->delete();

            return response()->json([
                'success' => true,
                'message' => 'Jadwal test Zoom berhasil dihapus.',
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal test Zoom tidak bisa dihapus.',
            ], 409);
        }
    }

    public function destroyGroup(string $groupKey): JsonResponse
    {
        $rows = $this->rowsByGroupKey($groupKey);

        if ($rows->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Data jadwal tidak ditemukan.',
            ], 404);
        }

        try {
            DB::transaction(function () use ($groupKey) {
                JadwalTestZoom::query()
                    ->where('group_key', $groupKey)
                    ->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Semua jadwal test Zoom pada group ini berhasil dihapus.',
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Group jadwal test Zoom tidak bisa dihapus.',
            ], 409);
        }
    }


    private function applyTanggalTestFilter($query, Request $request): void
    {
        $tanggalTest = $request->input('tanggal_test')
            ?: $request->input('filter_tanggal_test')
            ?: null;

        $tanggalMulai = $request->input('tanggal_test_mulai')
            ?: $tanggalTest
            ?: null;

        $tanggalSelesai = $request->input('tanggal_test_selesai')
            ?: $tanggalTest
            ?: null;

        $tanggalMulaiCarbon = $this->parseDate($tanggalMulai);
        $tanggalSelesaiCarbon = $this->parseDate($tanggalSelesai);

        if (!$tanggalMulaiCarbon && !$tanggalSelesaiCarbon) {
            return;
        }

        $query->where(function ($mainQuery) use ($tanggalMulaiCarbon, $tanggalSelesaiCarbon) {
            $mainQuery
                ->where(function ($jadwalMulaiQuery) use ($tanggalMulaiCarbon, $tanggalSelesaiCarbon) {
                    $jadwalMulaiQuery->whereNotNull('jadwal_mulai');

                    if ($tanggalMulaiCarbon) {
                        $jadwalMulaiQuery->whereDate(
                            'jadwal_mulai',
                            '>=',
                            $tanggalMulaiCarbon->toDateString()
                        );
                    }

                    if ($tanggalSelesaiCarbon) {
                        $jadwalMulaiQuery->whereDate(
                            'jadwal_mulai',
                            '<=',
                            $tanggalSelesaiCarbon->toDateString()
                        );
                    }
                })
                ->orWhere(function ($jadwalLegacyQuery) use ($tanggalMulaiCarbon, $tanggalSelesaiCarbon) {
                    $jadwalLegacyQuery
                        ->whereNull('jadwal_mulai')
                        ->whereNotNull('jadwal');

                    if ($tanggalMulaiCarbon) {
                        $jadwalLegacyQuery->whereDate(
                            'jadwal',
                            '>=',
                            $tanggalMulaiCarbon->toDateString()
                        );
                    }

                    if ($tanggalSelesaiCarbon) {
                        $jadwalLegacyQuery->whereDate(
                            'jadwal',
                            '<=',
                            $tanggalSelesaiCarbon->toDateString()
                        );
                    }
                });
        });
    }

    private function jadwalDetailQuery()
    {
        return JadwalTestZoom::query()
            ->with([
                'dataRiwayatDiri:id,nama_lengkap,nama_panggil,email,no_wa,token,tanggal_skrining,posisi_yang_dilamar,perusahaan_dilamar',
                'dataRiwayatDiri.posisi:id,nama_posisi',
                'dataRiwayatDiri.perusahaan:id,nama_perusahaan',
            ]);
    }

    private function rowsByGroupKey(string $groupKey)
    {
        $rows = $this->jadwalDetailQuery()
            ->where('group_key', $groupKey)
            ->get();

        if ($rows->isNotEmpty()) {
            return $rows;
        }

        $jadwal = $this->dateTimeFromOldGroupKey($groupKey);

        if (!$jadwal) {
            return collect();
        }

        return $this->jadwalDetailQuery()
            ->where('jadwal', $jadwal->toDateTimeString())
            ->get();
    }

    private function formatDetailGroup($rows, ?Carbon $jadwalMulai, ?Carbon $jadwalSelesai, string $sesi, string $groupKey): array
    {
        $first = $rows->first();

        return [
            'id' => $groupKey,
            'group_key' => $groupKey,

            'sesi' => $sesi,
            'sesi_label' => $sesi,

            'tanggal_test' => $jadwalMulai?->toDateString(),
            'tanggal_test_label' => $jadwalMulai
                ? $jadwalMulai->translatedFormat('d F Y')
                : '-',

            'jam_test' => $this->formatJamRange($jadwalMulai, $jadwalSelesai),

            'jadwal' => $jadwalMulai?->toDateTimeString(),
            'jadwal_mulai' => $jadwalMulai?->toDateTimeString(),
            'jadwal_selesai' => $jadwalSelesai?->toDateTimeString(),

            'jadwal_label' => $this->formatJadwalLabel($jadwalMulai, $jadwalSelesai, $sesi),

            'link_zoom' => $first?->link_zoom,
            'link_zoom_label' => $first?->link_zoom ?: '-',

            'total_pelamar' => $rows->count(),

            'total_hadir' => $rows
                ->filter(fn ($item) => $this->normalizeKehadiran($item->kehadiran ?? null) === 'hadir')
                ->count(),

            'total_tidak_hadir' => $rows
                ->filter(fn ($item) => $this->normalizeKehadiran($item->kehadiran ?? null) === 'tidak_hadir')
                ->count(),

            'total_belum_konfirmasi' => $rows
                ->filter(fn ($item) => !$this->normalizeKehadiran($item->kehadiran ?? null))
                ->count(),

            'pelamar' => $rows
                ->map(fn ($item) => $this->formatJadwalItem($item))
                ->values()
                ->toArray(),
        ];
    }

    private function formatJadwalItem(JadwalTestZoom $item): array
    {
        $jadwalMulai = $this->getJadwalMulai($item);
        $jadwalSelesai = $this->getJadwalSelesai($item);
        $sesi = $this->getSesi($item);
        $kehadiran = $this->normalizeKehadiran($item->kehadiran ?? null);

        return [
            'id' => $item->id,
            'data_riwayat_diri_id' => $item->data_riwayat_diri_id,

            'sesi' => $sesi,
            'sesi_label' => $sesi,

            'jadwal' => $jadwalMulai?->toDateTimeString(),
            'jadwal_mulai' => $jadwalMulai?->toDateTimeString(),
            'jadwal_selesai' => $jadwalSelesai?->toDateTimeString(),

            'jadwal_label' => $this->formatJadwalLabel($jadwalMulai, $jadwalSelesai, $sesi),
            'jam_test' => $this->formatJamRange($jadwalMulai, $jadwalSelesai),

            'link_zoom' => $item->link_zoom,
            'link_zoom_label' => $item->link_zoom ?: '-',

            'kehadiran' => $kehadiran,
            'kehadiran_label' => $this->kehadiranLabel($kehadiran),

            'data_riwayat_diri' => $item->dataRiwayatDiri,
        ];
    }

    private function mergeScheduleInputs(Request $request): void
    {
        $linkZoom = $request->input('link_zoom')
            ?: $request->input('url_link')
            ?: $request->input('link_url')
            ?: $request->input('zoom_link')
            ?: null;

        $jadwalMulai = $request->input('jadwal_mulai')
            ?: $request->input('jadwal')
            ?: null;

        $jadwalSelesai = $request->input('jadwal_selesai')
            ?: $request->input('jadwal_akhir')
            ?: null;

        $sesi = $request->input('sesi')
            ?: $request->input('nama_sesi')
            ?: 'Sesi 1';

        $request->merge([
            'link_zoom' => $linkZoom ? trim($linkZoom) : null,
            'sesi' => trim((string) $sesi),
            'jadwal_mulai' => $jadwalMulai,
            'jadwal_selesai' => $jadwalSelesai,
            'jadwal' => $jadwalMulai,
        ]);
    }

    private function validationMessages(): array
    {
        return [
            'tanggal_skrining.required' => 'Tanggal skrining wajib dipilih.',
            'tanggal_skrining.date' => 'Format tanggal skrining tidak valid.',

            'data_riwayat_diri_ids.required' => 'Minimal satu pelamar wajib dipilih.',
            'data_riwayat_diri_ids.array' => 'Format data pelamar tidak valid.',
            'data_riwayat_diri_ids.min' => 'Minimal satu pelamar wajib dipilih.',
            'data_riwayat_diri_ids.*.required' => 'Data pelamar tidak valid.',
            'data_riwayat_diri_ids.*.uuid' => 'Data pelamar tidak valid.',
            'data_riwayat_diri_ids.*.exists' => 'Data pelamar tidak ditemukan.',

            'data_riwayat_diri_id.required' => 'Pelamar wajib dipilih.',
            'data_riwayat_diri_id.uuid' => 'Data pelamar tidak valid.',
            'data_riwayat_diri_id.exists' => 'Pelamar tidak ditemukan.',

            'sesi.required' => 'Sesi jadwal wajib diisi.',
            'sesi.max' => 'Sesi jadwal maksimal 100 karakter.',

            'jadwal_mulai.required' => 'Jadwal mulai Zoom wajib diisi.',
            'jadwal_mulai.date' => 'Format jadwal mulai Zoom tidak valid.',

            'jadwal_selesai.required' => 'Jadwal akhir Zoom wajib diisi.',
            'jadwal_selesai.date' => 'Format jadwal akhir Zoom tidak valid.',
            'jadwal_selesai.after' => 'Jadwal akhir Zoom harus lebih besar dari jadwal mulai.',

            'link_zoom.url' => 'Format Link Zoom tidak valid. Gunakan URL lengkap, contoh: https://zoom.us/j/xxxx.',
            'link_zoom.max' => 'Link Zoom terlalu panjang.',
        ];
    }

    private function makeGroupKeyFromItem($item): string
    {
        if (!empty($item?->group_key)) {
            return (string) $item->group_key;
        }

        return $this->makeGroupKey(
            $this->getSesi($item),
            $this->getJadwalMulai($item),
            $this->getJadwalSelesai($item)
        );
    }

    private function makeGroupKey(string $sesi, ?Carbon $jadwalMulai, ?Carbon $jadwalSelesai): string
    {
        $mulai = $jadwalMulai?->format('YmdHis') ?: 'mulai';
        $selesai = $jadwalSelesai?->format('YmdHis') ?: 'selesai';
        $sesiKey = substr(md5(strtolower(trim($sesi))), 0, 10);

        return "{$mulai}_{$selesai}_{$sesiKey}";
    }

    private function getSesi($item): string
    {
        $sesi = trim((string) ($item?->sesi ?? ''));

        return $sesi !== '' ? $sesi : 'Sesi 1';
    }

    private function getJadwalMulai($item): ?Carbon
    {
        return $this->parseDateTime($item?->jadwal_mulai ?? $item?->jadwal ?? null);
    }

    private function getJadwalSelesai($item): ?Carbon
    {
        $mulai = $this->getJadwalMulai($item);
        $selesai = $this->parseDateTime($item?->jadwal_selesai ?? null);

        return $selesai ?: $mulai;
    }

    private function formatJamRange(?Carbon $mulai, ?Carbon $selesai): string
    {
        if (!$mulai) {
            return '-';
        }

        if (!$selesai || $selesai->equalTo($mulai)) {
            return $mulai->format('H:i');
        }

        return $mulai->format('H:i') . ' - ' . $selesai->format('H:i');
    }

    private function formatJadwalLabel(?Carbon $mulai, ?Carbon $selesai, string $sesi): string
    {
        if (!$mulai) {
            return '-';
        }

        return trim($sesi) . ' • ' . $mulai->translatedFormat('d F Y') . ' • ' . $this->formatJamRange($mulai, $selesai);
    }

    private function dateTimeFromOldGroupKey(string $groupKey): ?Carbon
    {
        $normalized = str_replace('_', ' ', $groupKey);

        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}-\d{2}-\d{2}$/', $normalized)) {
            $normalized = preg_replace(
                '/^(\d{4}-\d{2}-\d{2}) (\d{2})-(\d{2})-(\d{2})$/',
                '$1 $2:$3:$4',
                $normalized
            );
        }

        return $this->parseDateTime($normalized);
    }

    private function parseDateTime($value): ?Carbon
    {
        if (!$value) {
            return null;
        }

        try {
            return $value instanceof Carbon
                ? $value->copy()
                : Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function parseDate($value): ?Carbon
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }
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

    private function kehadiranLabel(?string $kehadiran): string
    {
        return match ($kehadiran) {
            'hadir' => 'Hadir',
            'tidak_hadir' => 'Tidak Hadir',
            default => 'Belum Konfirmasi',
        };
    }
}
