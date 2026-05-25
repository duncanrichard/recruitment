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
    public function list(): JsonResponse
    {
        $rows = JadwalTestZoom::query()
            ->with([
                'dataRiwayatDiri:id,nama_lengkap,nama_panggil,email,no_wa,token,tanggal_skrining,posisi_yang_dilamar,perusahaan_dilamar',
                'dataRiwayatDiri.posisi:id,nama_posisi',
                'dataRiwayatDiri.perusahaan:id,nama_perusahaan',
            ])
            ->latest('jadwal')
            ->get();

        $data = $rows
            ->groupBy(function ($item) {
                return $this->makeGroupKey($item->jadwal);
            })
            ->map(function ($items, $groupKey) {
                $first = $items->first();
                $jadwal = $this->parseDateTime($first?->jadwal);

                return [
                    'id' => $groupKey,
                    'group_key' => $groupKey,

                    'tanggal_test' => $jadwal?->toDateString(),
                    'tanggal_test_label' => $jadwal
                        ? $jadwal->translatedFormat('d F Y')
                        : '-',

                    'jam_test' => $jadwal
                        ? $jadwal->format('H:i')
                        : '-',

                    'jadwal' => $jadwal
                        ? $jadwal->toDateTimeString()
                        : null,

                    'jadwal_label' => $jadwal
                        ? $jadwal->translatedFormat('d F Y H:i')
                        : '-',

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
        $jadwal = $this->dateTimeFromGroupKey($groupKey);

        if (!$jadwal) {
            return response()->json([
                'success' => false,
                'message' => 'Group jadwal tidak valid.',
            ], 422);
        }

        $rows = $this->jadwalDetailQuery()
            ->where('jadwal', $jadwal->toDateTimeString())
            ->get()
            ->sortBy(function ($item) {
                return strtolower($item->dataRiwayatDiri->nama_lengkap ?? '');
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $this->formatDetailGroup($rows, $jadwal, $groupKey),
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
            ->whereDate('jadwal', $tanggalCarbon->toDateString())
            ->latest('jadwal')
            ->get()
            ->sortBy(function ($item) {
                return strtolower($item->dataRiwayatDiri->nama_lengkap ?? '');
            })
            ->values();

        $data = $rows
            ->groupBy(function ($item) {
                return $this->makeGroupKey($item->jadwal);
            })
            ->map(function ($items, $groupKey) {
                $first = $items->first();
                $jadwal = $this->parseDateTime($first?->jadwal);

                return $this->formatDetailGroup($items, $jadwal, $groupKey);
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
        $this->mergeLinkZoom($request);

        $validated = $request->validate([
            'tanggal_skrining' => [
                'required',
                'date',
            ],
            'data_riwayat_diri_ids' => [
                'required',
                'array',
                'min:1',
            ],
            'data_riwayat_diri_ids.*' => [
                'required',
                'uuid',
                Rule::exists('data_riwayat_diri', 'id'),
            ],
            'jadwal' => [
                'required',
                'date',
            ],
            'link_zoom' => [
                'nullable',
                'url',
                'max:2048',
            ],
        ], [
            'tanggal_skrining.required' => 'Tanggal skrining wajib dipilih.',
            'tanggal_skrining.date' => 'Format tanggal skrining tidak valid.',

            'data_riwayat_diri_ids.required' => 'Minimal satu pelamar wajib dipilih.',
            'data_riwayat_diri_ids.array' => 'Format data pelamar tidak valid.',
            'data_riwayat_diri_ids.min' => 'Minimal satu pelamar wajib dipilih.',
            'data_riwayat_diri_ids.*.required' => 'Data pelamar tidak valid.',
            'data_riwayat_diri_ids.*.uuid' => 'Data pelamar tidak valid.',
            'data_riwayat_diri_ids.*.exists' => 'Data pelamar tidak ditemukan.',

            'jadwal.required' => 'Jadwal Zoom wajib diisi.',
            'jadwal.date' => 'Format jadwal Zoom tidak valid.',

            'link_zoom.url' => 'Format Link Zoom tidak valid. Gunakan URL lengkap, contoh: https://zoom.us/j/xxxx.',
            'link_zoom.max' => 'Link Zoom terlalu panjang.',
        ]);

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

        $created = DB::transaction(function () use ($pelamarBelumDijadwalkan, $validated) {
            $items = [];

            foreach ($pelamarBelumDijadwalkan as $pelamarId) {
                $items[] = JadwalTestZoom::query()->create([
                    'data_riwayat_diri_id' => $pelamarId,
                    'jadwal' => Carbon::parse($validated['jadwal'])->toDateTimeString(),
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
            ->latest('jadwal')
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
        $this->mergeLinkZoom($request);

        $data = JadwalTestZoom::query()->findOrFail($id);

        $validated = $request->validate([
            'data_riwayat_diri_id' => [
                'required',
                'uuid',
                Rule::exists('data_riwayat_diri', 'id'),
            ],
            'jadwal' => [
                'required',
                'date',
            ],
            'link_zoom' => [
                'nullable',
                'url',
                'max:2048',
            ],
        ], [
            'data_riwayat_diri_id.required' => 'Pelamar wajib dipilih.',
            'data_riwayat_diri_id.uuid' => 'Data pelamar tidak valid.',
            'data_riwayat_diri_id.exists' => 'Pelamar tidak ditemukan.',

            'jadwal.required' => 'Jadwal Zoom wajib diisi.',
            'jadwal.date' => 'Format jadwal Zoom tidak valid.',

            'link_zoom.url' => 'Format Link Zoom tidak valid. Gunakan URL lengkap, contoh: https://zoom.us/j/xxxx.',
            'link_zoom.max' => 'Link Zoom terlalu panjang.',
        ]);

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

        $oldJadwal = $this->parseDateTime($data->jadwal);
        $newJadwal = Carbon::parse($validated['jadwal'])->toDateTimeString();

        DB::transaction(function () use ($data, $validated, $oldJadwal, $newJadwal) {
            $data->forceFill([
                'data_riwayat_diri_id' => $validated['data_riwayat_diri_id'],
                'jadwal' => $newJadwal,
                'link_zoom' => $validated['link_zoom'] ?? null,
            ])->save();

            if ($oldJadwal) {
                JadwalTestZoom::query()
                    ->where('id', '!=', $data->id)
                    ->where('jadwal', $oldJadwal->toDateTimeString())
                    ->update([
                        'jadwal' => $newJadwal,
                        'link_zoom' => $validated['link_zoom'] ?? null,
                    ]);
            }
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
        $this->mergeLinkZoom($request);

        $oldJadwal = $this->dateTimeFromGroupKey($groupKey);

        if (!$oldJadwal) {
            return response()->json([
                'success' => false,
                'message' => 'Group jadwal tidak valid.',
            ], 422);
        }

        $validated = $request->validate([
            'jadwal' => [
                'required',
                'date',
            ],
            'link_zoom' => [
                'nullable',
                'url',
                'max:2048',
            ],
        ], [
            'jadwal.required' => 'Jadwal Zoom wajib diisi.',
            'jadwal.date' => 'Format jadwal Zoom tidak valid.',

            'link_zoom.url' => 'Format Link Zoom tidak valid. Gunakan URL lengkap, contoh: https://zoom.us/j/xxxx.',
            'link_zoom.max' => 'Link Zoom terlalu panjang.',
        ]);

        $rows = JadwalTestZoom::query()
            ->where('jadwal', $oldJadwal->toDateTimeString())
            ->get();

        if ($rows->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Data jadwal tidak ditemukan.',
            ], 404);
        }

        $newJadwal = Carbon::parse($validated['jadwal'])->toDateTimeString();
        $newLinkZoom = $validated['link_zoom'] ?? null;

        DB::transaction(function () use ($oldJadwal, $newJadwal, $newLinkZoom) {
            JadwalTestZoom::query()
                ->where('jadwal', $oldJadwal->toDateTimeString())
                ->update([
                    'jadwal' => $newJadwal,
                    'link_zoom' => $newLinkZoom,
                ]);
        });

        $newGroupKey = $this->makeGroupKey($newJadwal);

        $freshRows = $this->jadwalDetailQuery()
            ->where('jadwal', $newJadwal)
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
                Carbon::parse($newJadwal),
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
        $jadwal = $this->dateTimeFromGroupKey($groupKey);

        if (!$jadwal) {
            return response()->json([
                'success' => false,
                'message' => 'Group jadwal tidak valid.',
            ], 422);
        }

        $rows = JadwalTestZoom::query()
            ->where('jadwal', $jadwal->toDateTimeString())
            ->get();

        if ($rows->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Data jadwal tidak ditemukan.',
            ], 404);
        }

        try {
            DB::transaction(function () use ($jadwal) {
                JadwalTestZoom::query()
                    ->where('jadwal', $jadwal->toDateTimeString())
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

    private function jadwalDetailQuery()
    {
        return JadwalTestZoom::query()
            ->with([
                'dataRiwayatDiri:id,nama_lengkap,nama_panggil,email,no_wa,token,tanggal_skrining,posisi_yang_dilamar,perusahaan_dilamar',
                'dataRiwayatDiri.posisi:id,nama_posisi',
                'dataRiwayatDiri.perusahaan:id,nama_perusahaan',
            ]);
    }

    private function formatDetailGroup($rows, ?Carbon $jadwal, string $groupKey): array
    {
        $first = $rows->first();

        return [
            'id' => $groupKey,
            'group_key' => $groupKey,

            'tanggal_test' => $jadwal?->toDateString(),
            'tanggal_test_label' => $jadwal
                ? $jadwal->translatedFormat('d F Y')
                : '-',

            'jam_test' => $jadwal
                ? $jadwal->format('H:i')
                : '-',

            'jadwal' => $jadwal
                ? $jadwal->toDateTimeString()
                : null,

            'jadwal_label' => $jadwal
                ? $jadwal->translatedFormat('d F Y H:i')
                : '-',

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
        $jadwal = $this->parseDateTime($item->jadwal);
        $kehadiran = $this->normalizeKehadiran($item->kehadiran ?? null);

        return [
            'id' => $item->id,
            'data_riwayat_diri_id' => $item->data_riwayat_diri_id,

            'jadwal' => $jadwal?->toDateTimeString(),
            'jadwal_label' => $jadwal
                ? $jadwal->translatedFormat('d F Y H:i')
                : '-',

            'link_zoom' => $item->link_zoom,
            'link_zoom_label' => $item->link_zoom ?: '-',

            'kehadiran' => $kehadiran,
            'kehadiran_label' => $this->kehadiranLabel($kehadiran),

            'data_riwayat_diri' => $item->dataRiwayatDiri,
        ];
    }

    private function makeGroupKey($value): string
    {
        $date = $this->parseDateTime($value);

        if (!$date) {
            return 'jadwal_tidak_valid';
        }

        return $date->format('Y-m-d_H-i-s');
    }

    private function dateTimeFromGroupKey(string $groupKey): ?Carbon
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

    private function mergeLinkZoom(Request $request): void
    {
        $linkZoom = $request->input('link_zoom')
            ?: $request->input('url_link')
            ?: $request->input('link_url')
            ?: $request->input('zoom_link')
            ?: null;

        $request->merge([
            'link_zoom' => $linkZoom ? trim($linkZoom) : null,
        ]);
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