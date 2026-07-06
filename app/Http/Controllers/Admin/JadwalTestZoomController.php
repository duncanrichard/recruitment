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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
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

        $query = $this->scopedJadwalTestZoomQuery()
            ->with([
                'dataRiwayatDiri:id,nama_lengkap,nama_panggil,email,no_wa,token,tanggal_skrining,posisi_yang_dilamar,perusahaan_dilamar',
                'dataRiwayatDiri.posisi:id,nama_posisi',
                'dataRiwayatDiri.perusahaan:id,nama_perusahaan,no_wa,token_api_wa',
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
            ->where(function ($query) use ($tanggalCarbon) {
                $query
                    ->whereDate('jadwal_mulai', $tanggalCarbon->toDateString())
                    ->orWhere(function ($legacyQuery) use ($tanggalCarbon) {
                        $legacyQuery
                            ->whereNull('jadwal_mulai')
                            ->whereDate('jadwal', $tanggalCarbon->toDateString());
                    });
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

        $data = $this->scopedPelamarQuery()
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

        $pelamarIdsSesuaiTanggal = $this->scopedPelamarQuery()
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

        /*
        |--------------------------------------------------------------------------
        | Kirim WhatsApp otomatis setelah jadwal berhasil dibuat
        |--------------------------------------------------------------------------
        | Pesan dikirim memakai token_api_wa dan no_wa dari perusahaan masing-masing
        | kandidat. Proses kirim WA tidak membatalkan penyimpanan jadwal.
        */
        $waResult = $this->sendJadwalZoomMessages($freshData);

        $message = count($created) . ' jadwal test Zoom berhasil disimpan.'
            . ($jumlahSkip > 0 ? ' ' . $jumlahSkip . ' pelamar dilewati karena sudah dijadwalkan.' : '');

        if (($waResult['total_dikirim'] ?? 0) > 0 && ($waResult['total_gagal_provider'] ?? 0) === 0) {
            $message .= ' Pesan WhatsApp jadwal Zoom berhasil dikirim ke kandidat sesuai perusahaan masing-masing.';
        } elseif (($waResult['total_dikirim'] ?? 0) > 0) {
            $message .= ' Sebagian pesan WhatsApp berhasil dikirim, sebagian gagal. Cek detail wa_result.';
        } else {
            $message .= ' Jadwal tersimpan, tetapi pesan WhatsApp belum terkirim. Cek detail wa_result.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $freshData,
            'wa_result' => $waResult,
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $this->mergeScheduleInputs($request);

        $data = $this->findScopedJadwalOrFail($id);

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

        if (!$this->scopedPelamarQuery()->where('id', $validated['data_riwayat_diri_id'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Pelamar tidak sesuai dengan perusahaan account yang login.',
            ], 422);
        }

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

            $this->scopedJadwalTestZoomQuery()
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

        $rowIds = $rows->pluck('id')->filter()->values()->all();

        DB::transaction(function () use (
            $rowIds,
            $validated,
            $jadwalMulai,
            $jadwalSelesai,
            $newGroupKey,
            $newLinkZoom
        ) {
            JadwalTestZoom::query()
                ->whereIn('id', $rowIds)
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

        /*
        |--------------------------------------------------------------------------
        | Kirim WhatsApp otomatis setelah group jadwal berhasil diperbarui
        |--------------------------------------------------------------------------
        | Saat group test Zoom di-update, semua kandidat pada group tersebut akan
        | menerima pesan jadwal terbaru. Pengiriman tetap menggunakan token_api_wa
        | dan no_wa dari perusahaan masing-masing kandidat. Jika WA gagal, update
        | jadwal tetap berhasil dan detail kegagalan dikembalikan pada wa_result.
        */
        $waResult = $this->sendJadwalZoomMessages($freshRows);

        $message = 'Group jadwal test Zoom berhasil diperbarui.';

        if (($waResult['total_dikirim'] ?? 0) > 0 && ($waResult['total_gagal_provider'] ?? 0) === 0) {
            $message .= ' Pesan WhatsApp jadwal Zoom terbaru berhasil dikirim ke kandidat sesuai perusahaan masing-masing.';
        } elseif (($waResult['total_dikirim'] ?? 0) > 0) {
            $message .= ' Sebagian pesan WhatsApp berhasil dikirim, sebagian gagal. Cek detail wa_result.';
        } else {
            $message .= ' Jadwal sudah diperbarui, tetapi pesan WhatsApp belum terkirim. Cek detail wa_result.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $this->formatDetailGroup(
                $freshRows,
                $jadwalMulai,
                $jadwalSelesai,
                $validated['sesi'],
                $newGroupKey
            ),
            'wa_result' => $waResult,
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $data = $this->findScopedJadwalOrFail($id);

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
            $rowIds = $rows->pluck('id')->filter()->values()->all();

            DB::transaction(function () use ($rowIds) {
                JadwalTestZoom::query()
                    ->whereIn('id', $rowIds)
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



    private function sendJadwalZoomMessages($jadwals, ?string $template = null): array
    {
        $jadwals = collect($jadwals)
            ->filter(fn ($item) => $item instanceof JadwalTestZoom)
            ->values();

        if ($jadwals->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Tidak ada jadwal Zoom untuk dikirim pesan.',
                'total_data' => 0,
                'total_dikirim' => 0,
                'total_dilewati' => 0,
                'total_gagal_provider' => 0,
                'total_perusahaan' => 0,
                'skipped' => [],
                'targets' => [],
                'perusahaan_responses' => [],
            ];
        }

        $groupedMessages = [];
        $skipped = [];
        $validatedCompanies = [];

        foreach ($jadwals as $jadwal) {
            $pelamar = $jadwal->dataRiwayatDiri;

            if (!$pelamar) {
                $skipped[] = [
                    'jadwal_id' => $jadwal->id,
                    'data_riwayat_diri_id' => $jadwal->data_riwayat_diri_id,
                    'reason' => 'Data kandidat tidak ditemukan pada jadwal.',
                ];

                continue;
            }

            $target = $this->normalizeWhatsappNumber($pelamar->no_wa ?? null);
            $perusahaan = $pelamar->perusahaan;

            if (!$target) {
                $skipped[] = [
                    'jadwal_id' => $jadwal->id,
                    'data_riwayat_diri_id' => $pelamar->id,
                    'nama_lengkap' => $pelamar->nama_lengkap,
                    'no_wa' => $pelamar->no_wa,
                    'perusahaan' => $perusahaan->nama_perusahaan ?? null,
                    'reason' => 'Nomor WhatsApp kandidat kosong atau tidak valid.',
                ];

                continue;
            }

            if (!$perusahaan) {
                $skipped[] = [
                    'jadwal_id' => $jadwal->id,
                    'data_riwayat_diri_id' => $pelamar->id,
                    'nama_lengkap' => $pelamar->nama_lengkap,
                    'no_wa' => $pelamar->no_wa,
                    'target' => $target,
                    'reason' => 'Data perusahaan kandidat tidak ditemukan.',
                ];

                continue;
            }

            $tokenApiWa = $this->normalizeTokenApiWa($perusahaan->token_api_wa ?? null);
            $nomerPerusahaan = $this->normalizeWhatsappNumber($perusahaan->no_wa ?? null);

            if (!$nomerPerusahaan) {
                $skipped[] = [
                    'jadwal_id' => $jadwal->id,
                    'data_riwayat_diri_id' => $pelamar->id,
                    'nama_lengkap' => $pelamar->nama_lengkap,
                    'no_wa' => $pelamar->no_wa,
                    'target' => $target,
                    'perusahaan_id' => $perusahaan->id ?? null,
                    'perusahaan' => $perusahaan->nama_perusahaan ?? null,
                    'reason' => 'Nomor WhatsApp perusahaan kosong atau tidak valid.',
                ];

                continue;
            }

            if (!$tokenApiWa) {
                $skipped[] = [
                    'jadwal_id' => $jadwal->id,
                    'data_riwayat_diri_id' => $pelamar->id,
                    'nama_lengkap' => $pelamar->nama_lengkap,
                    'no_wa' => $pelamar->no_wa,
                    'target' => $target,
                    'perusahaan_id' => $perusahaan->id ?? null,
                    'perusahaan' => $perusahaan->nama_perusahaan ?? null,
                    'nomer_perusahaan' => $nomerPerusahaan,
                    'reason' => 'Token API WA perusahaan kosong.',
                ];

                continue;
            }

            $companyValidationKey = (string) ($perusahaan->id ?? $tokenApiWa);

            if (!isset($validatedCompanies[$companyValidationKey])) {
                $validatedCompanies[$companyValidationKey] = $this->checkFonnteCredentialForSending(
                    $nomerPerusahaan,
                    $tokenApiWa
                );
            }

            $validasiWa = $validatedCompanies[$companyValidationKey];

            if (!$validasiWa['success']) {
                $skipped[] = [
                    'jadwal_id' => $jadwal->id,
                    'data_riwayat_diri_id' => $pelamar->id,
                    'nama_lengkap' => $pelamar->nama_lengkap,
                    'no_wa' => $pelamar->no_wa,
                    'target' => $target,
                    'perusahaan_id' => $perusahaan->id ?? null,
                    'perusahaan' => $perusahaan->nama_perusahaan ?? null,
                    'nomer_perusahaan' => $nomerPerusahaan,
                    'reason' => $validasiWa['message'],
                    'fonnte_device' => $validasiWa['device_number'] ?? null,
                    'fonnte_device_status' => $validasiWa['device_status'] ?? null,
                    'fonnte_response' => $validasiWa['fonnte_response'] ?? null,
                ];

                continue;
            }

            $groupKey = (string) ($perusahaan->id ?? $tokenApiWa);

            if (!isset($groupedMessages[$groupKey])) {
                $groupedMessages[$groupKey] = [
                    'perusahaan_id' => $perusahaan->id ?? null,
                    'perusahaan' => $perusahaan->nama_perusahaan ?? null,
                    'nomer_perusahaan' => $nomerPerusahaan,
                    'token_api_wa' => $tokenApiWa,
                    'fonnte_device' => $validasiWa['device_number'] ?? null,
                    'fonnte_device_status' => $validasiWa['device_status'] ?? null,
                    'messages' => [],
                ];
            }

            $groupedMessages[$groupKey]['messages'][] = [
                'target' => $target,
                'message' => $this->buildPesanJadwalZoom($jadwal, $pelamar, $template),
                'delay' => '2',
            ];
        }

        if (empty($groupedMessages)) {
            return [
                'success' => false,
                'message' => 'Tidak ada data valid untuk dikirim pesan WhatsApp jadwal Zoom.',
                'total_data' => $jadwals->count(),
                'total_dikirim' => 0,
                'total_dilewati' => count($skipped),
                'total_gagal_provider' => 0,
                'total_perusahaan' => 0,
                'skipped' => $skipped,
                'targets' => [],
                'perusahaan_responses' => [],
            ];
        }

        $responses = [];
        $totalDikirim = 0;
        $totalGagalProvider = 0;
        $targets = [];

        foreach ($groupedMessages as $group) {
            try {
                $response = Http::asForm()
                    ->withoutVerifying()
                    ->withHeaders([
                        'Authorization' => $group['token_api_wa'],
                    ])
                    ->timeout(120)
                    ->post('https://api.fonnte.com/send', [
                        'data' => json_encode($group['messages']),
                        'countryCode' => '62',
                        'typing' => 'false',
                        'preview' => 'true',
                    ]);

                $json = $response->json();
                $fonnteStatus = $json['status'] ?? $json['Status'] ?? false;
                $isSuccess = $response->successful() && (bool) $fonnteStatus;
                $countMessages = count($group['messages']);

                if ($isSuccess) {
                    $totalDikirim += $countMessages;
                } else {
                    $totalGagalProvider += $countMessages;
                }

                $groupTargets = collect($group['messages'])->pluck('target')->values()->all();
                $targets = array_merge($targets, $groupTargets);

                $responses[] = [
                    'success' => $isSuccess,
                    'perusahaan_id' => $group['perusahaan_id'],
                    'perusahaan' => $group['perusahaan'],
                    'nomer_perusahaan' => $group['nomer_perusahaan'],
                    'fonnte_device' => $group['fonnte_device'] ?? null,
                    'fonnte_device_status' => $group['fonnte_device_status'] ?? null,
                    'total_data' => $countMessages,
                    'targets' => $groupTargets,
                    'fonnte_http_code' => $response->status(),
                    'fonnte_response' => $json ?: $response->body(),
                    'message' => $isSuccess
                        ? 'Pesan jadwal Zoom berhasil dikirim ke antrean Fonnte untuk perusahaan ini.'
                        : ($json['reason'] ?? $json['detail'] ?? $json['message'] ?? 'Pesan jadwal Zoom gagal dikirim melalui Fonnte untuk perusahaan ini.'),
                ];
            } catch (\Throwable $e) {
                $countMessages = count($group['messages']);
                $totalGagalProvider += $countMessages;

                Log::error('Gagal mengirim pesan Fonnte jadwal Zoom per perusahaan', [
                    'message' => $e->getMessage(),
                    'perusahaan_id' => $group['perusahaan_id'],
                    'perusahaan' => $group['perusahaan'],
                ]);

                $responses[] = [
                    'success' => false,
                    'perusahaan_id' => $group['perusahaan_id'],
                    'perusahaan' => $group['perusahaan'],
                    'nomer_perusahaan' => $group['nomer_perusahaan'],
                    'fonnte_device' => $group['fonnte_device'] ?? null,
                    'fonnte_device_status' => $group['fonnte_device_status'] ?? null,
                    'total_data' => $countMessages,
                    'targets' => collect($group['messages'])->pluck('target')->values()->all(),
                    'message' => 'Terjadi kesalahan saat mengirim pesan Fonnte untuk perusahaan ini: ' . $e->getMessage(),
                ];
            }
        }

        $isAllSuccess = $totalDikirim > 0 && $totalGagalProvider === 0;
        $isPartialSuccess = $totalDikirim > 0 && $totalGagalProvider > 0;

        return [
            'success' => $totalDikirim > 0,
            'message' => $isAllSuccess
                ? 'Pesan WhatsApp jadwal Zoom berhasil dikirim sesuai perusahaan masing-masing.'
                : ($isPartialSuccess
                    ? 'Sebagian pesan WhatsApp jadwal Zoom berhasil dikirim, sebagian gagal.'
                    : 'Pesan WhatsApp jadwal Zoom gagal dikirim.'),
            'total_data' => $jadwals->count(),
            'total_dikirim' => $totalDikirim,
            'total_dilewati' => count($skipped),
            'total_gagal_provider' => $totalGagalProvider,
            'total_perusahaan' => count($groupedMessages),
            'skipped' => $skipped,
            'targets' => array_values(array_unique($targets)),
            'perusahaan_responses' => $responses,
        ];
    }

    private function buildPesanJadwalZoom(JadwalTestZoom $jadwal, DataRiwayatDiri $pelamar, ?string $template = null): string
    {
        $jadwalMulai = $this->getJadwalMulai($jadwal);
        $jadwalSelesai = $this->getJadwalSelesai($jadwal);
        $sesi = $this->getSesi($jadwal);
        $perusahaan = $pelamar->perusahaan?->nama_perusahaan ?: '-';
        $posisi = $pelamar->posisi?->nama_posisi ?: '-';
        $nama = $pelamar->nama_panggil ?: ($pelamar->nama_lengkap ?: 'Kandidat');
        $namaLengkap = $pelamar->nama_lengkap ?: $nama;
        $linkZoom = $jadwal->link_zoom ?: '-';
        $nomerPerusahaan = $this->normalizeWhatsappNumber($pelamar->perusahaan?->no_wa ?? null);
        $nomerPerusahaanLabel = $nomerPerusahaan ?: ($pelamar->perusahaan?->no_wa ?? '-');

        $tanggalTest = $jadwalMulai
            ? $jadwalMulai->translatedFormat('d F Y')
            : '-';

        $jamTest = $this->formatJamRange($jadwalMulai, $jadwalSelesai);
        $jadwalLabel = $this->formatJadwalLabel($jadwalMulai, $jadwalSelesai, $sesi);

        $message = $template ?: "Halo {nama},\n\n"
            . "Anda dijadwalkan mengikuti test melalui Zoom untuk posisi {posisi} di {perusahaan}.\n\n"
            . "Detail jadwal:\n"
            . "Sesi: {sesi}\n"
            . "Tanggal: {tanggal_test}\n"
            . "Jam: {jam_test}\n"
            . "Link Zoom: {link_zoom}\n\n"
            . "Mohon hadir tepat waktu dan pastikan koneksi internet, kamera, serta audio berjalan dengan baik.\n\n"
            . "Jika ada kendala, silakan hubungi WhatsApp ini.\n\n"
            . "Terima kasih.\n"
            . "Tim Rekrutmen {perusahaan}";

        return strtr($message, [
            '{nama}' => $nama,
            '{nama_lengkap}' => $namaLengkap,
            '{posisi}' => $posisi,
            '{perusahaan}' => $perusahaan,
            '{sesi}' => $sesi,
            '{tanggal_test}' => $tanggalTest,
            '{jam_test}' => $jamTest,
            '{jadwal}' => $jadwalLabel,
            '{jadwal_zoom}' => $jadwalLabel,
            '{link_zoom}' => $linkZoom,
            '{nomer_perusahaan}' => (string) $nomerPerusahaanLabel,
            '{nomor_perusahaan}' => (string) $nomerPerusahaanLabel,
            '{no_wa_perusahaan}' => (string) $nomerPerusahaanLabel,
        ]);
    }

    private function checkFonnteCredentialForSending(?string $nomerPerusahaan, ?string $tokenApiWa): array
    {
        if (!$nomerPerusahaan) {
            return [
                'success' => false,
                'message' => 'Nomor WhatsApp perusahaan kosong atau tidak valid.',
            ];
        }

        if (!$tokenApiWa) {
            return [
                'success' => false,
                'message' => 'Token API WA perusahaan kosong.',
            ];
        }

        try {
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Authorization' => $tokenApiWa,
                ])
                ->timeout(30)
                ->post('https://api.fonnte.com/device');

            $json = $response->json();

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Gagal memvalidasi token API WA perusahaan ke Fonnte.',
                    'fonnte_response' => $json ?: $response->body(),
                ];
            }

            if (!($json['status'] ?? false)) {
                return [
                    'success' => false,
                    'message' => $json['reason']
                        ?? $json['message']
                        ?? 'Token API WA perusahaan tidak valid.',
                    'fonnte_response' => $json,
                ];
            }

            $deviceNumber = $this->normalizeWhatsappNumber($json['device'] ?? null);

            if (!$deviceNumber) {
                return [
                    'success' => false,
                    'message' => 'Nomor device pada token API WA tidak ditemukan.',
                    'fonnte_response' => $json,
                ];
            }

            if ($deviceNumber !== $nomerPerusahaan) {
                return [
                    'success' => false,
                    'message' => 'Token API WA tidak sesuai dengan nomor perusahaan. Token ini terdaftar untuk nomor ' . ($json['device'] ?? '-') . '.',
                    'device_number' => $deviceNumber,
                    'device_status' => $json['device_status'] ?? null,
                    'fonnte_response' => $json,
                ];
            }

            if (($json['device_status'] ?? null) !== 'connect') {
                return [
                    'success' => false,
                    'message' => 'Device WhatsApp perusahaan belum connect.',
                    'device_number' => $deviceNumber,
                    'device_status' => $json['device_status'] ?? null,
                    'fonnte_response' => $json,
                ];
            }

            return [
                'success' => true,
                'message' => 'Token API WA dan nomor perusahaan valid.',
                'device_number' => $deviceNumber,
                'device_status' => $json['device_status'] ?? null,
                'fonnte_response' => $json,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Gagal memvalidasi token API WA perusahaan: ' . $e->getMessage(),
            ];
        }
    }

    private function normalizeTokenApiWa(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }

    private function normalizeWhatsappNumber(?string $number): ?string
    {
        if (!$number) {
            return null;
        }

        $number = trim($number);
        $number = preg_replace('/[^0-9+]/', '', $number);

        if ($number === '') {
            return null;
        }

        if (Str::startsWith($number, '+')) {
            $number = substr($number, 1);
        }

        if (Str::startsWith($number, '0')) {
            $number = '62' . substr($number, 1);
        }

        if (Str::startsWith($number, '8')) {
            $number = '62' . $number;
        }

        if (!preg_match('/^62[0-9]{8,15}$/', $number)) {
            return null;
        }

        return $number;
    }


    private function scopedPelamarQuery()
    {
        $query = DataRiwayatDiri::query();

        $allowedPerusahaanIds = $this->currentUserPerusahaanIds();

        if (is_array($allowedPerusahaanIds)) {
            if (empty($allowedPerusahaanIds)) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('perusahaan_dilamar', $allowedPerusahaanIds);
            }
        }

        return $query;
    }

    private function scopedJadwalTestZoomQuery()
    {
        $query = JadwalTestZoom::query();

        $allowedPerusahaanIds = $this->currentUserPerusahaanIds();

        if (is_array($allowedPerusahaanIds)) {
            if (empty($allowedPerusahaanIds)) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereHas('dataRiwayatDiri', function ($pelamarQuery) use ($allowedPerusahaanIds) {
                    $pelamarQuery->whereIn('perusahaan_dilamar', $allowedPerusahaanIds);
                });
            }
        }

        return $query;
    }

    private function findScopedJadwalOrFail(string $id): JadwalTestZoom
    {
        return $this->scopedJadwalTestZoomQuery()->findOrFail($id);
    }

    /**
     * Return:
     * - null  => user boleh akses semua perusahaan, contoh Superadmin.
     * - array => user hanya boleh akses perusahaan yang terhubung ke account login.
     */
    private function currentUserPerusahaanIds(): ?array
    {
        $user = Auth::user();

        if (!$user) {
            return [];
        }

        if ($this->currentUserCanAccessAllPerusahaan($user)) {
            return null;
        }

        $ids = [];

        try {
            if (method_exists($user, 'perusahaans')) {
                $ids = $user->perusahaans()
                    ->pluck('data_perusahaan.id')
                    ->map(function ($id) {
                        return (string) $id;
                    })
                    ->values()
                    ->all();
            }
        } catch (\Throwable $th) {
            $ids = [];
        }

        if (empty($ids) && !empty($user->perusahaan_id)) {
            $ids[] = (string) $user->perusahaan_id;
        }

        return collect($ids)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function currentUserCanAccessAllPerusahaan($user): bool
    {
        try {
            if (method_exists($user, 'hasRole')) {
                if ($user->hasRole(['superadmin', 'Superadmin', 'super admin', 'Super Admin'])) {
                    return true;
                }
            }
        } catch (\Throwable $th) {
            //
        }

        try {
            if (method_exists($user, 'roles')) {
                $roleNames = $user->roles()
                    ->pluck('name')
                    ->map(function ($name) {
                        return strtolower(trim((string) $name));
                    })
                    ->values()
                    ->all();

                return collect($roleNames)->contains(function ($name) {
                    return in_array($name, [
                        'superadmin',
                        'super admin',
                    ], true);
                });
            }
        } catch (\Throwable $th) {
            //
        }

        return false;
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
        return $this->scopedJadwalTestZoomQuery()
            ->with([
                'dataRiwayatDiri:id,nama_lengkap,nama_panggil,email,no_wa,token,tanggal_skrining,posisi_yang_dilamar,perusahaan_dilamar',
                'dataRiwayatDiri.posisi:id,nama_posisi',
                'dataRiwayatDiri.perusahaan:id,nama_perusahaan,no_wa,token_api_wa',
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
