<?php

namespace App\Http\Controllers\Admin\JadwalTest;

use App\Http\Controllers\Controller;
use App\Models\JadwalTestMmpi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
        $request->merge([
            'tanggal_mulai' => $request->input('tanggal_mulai')
                ?: $request->input('tanggal_test_mmpi_mulai')
                ?: $request->input('tanggal_test_mulai'),

            'tanggal_selesai' => $request->input('tanggal_selesai')
                ?: $request->input('tanggal_test_mmpi_selesai')
                ?: $request->input('tanggal_test_selesai'),
        ]);

        $validated = $request->validate([
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
        ], [
            'tanggal_mulai.date' => 'Format Tanggal Test MMPI Mulai tidak valid.',
            'tanggal_selesai.date' => 'Format Tanggal Test MMPI Selesai tidak valid.',
            'tanggal_selesai.after_or_equal' => 'Tanggal Test MMPI Selesai tidak boleh lebih kecil dari Tanggal Test MMPI Mulai.',
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

        $this->applyCompanyScopeToDrdJoin($query, 'drd');

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

        $this->applyCompanyScopeToDrdJoin($query, 'drd');

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
                $daftarHadirQuery = DB::table('daftar_hadir_test_zoom as dh')
                    ->join('jadwal_test_zoom as jtz', function ($join) {
                        $join->on('jtz.id', '=', 'dh.jadwal_test_zoom_id')
                            ->whereNull('jtz.deleted_at');
                    })
                    ->join('data_riwayat_diri as drd', 'drd.id', '=', 'dh.data_riwayat_diri_id')
                    ->where('dh.id', $item['daftar_hadir_test_zoom_id'])
                    ->whereNull('dh.deleted_at');

                if (Schema::hasColumn('data_riwayat_diri', 'deleted_at')) {
                    $daftarHadirQuery->whereNull('drd.deleted_at');
                }

                $this->applyCompanyScopeToDrdJoin($daftarHadirQuery, 'drd');

                $daftarHadir = $daftarHadirQuery
                    ->select([
                        'dh.id',
                        'dh.data_riwayat_diri_id',
                        'dh.status_kehadiran',
                        'dh.hasil_test',
                        'dh.jadwal_test_zoom_id',
                        'drd.perusahaan_dilamar',
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

        $waResult = $this->sendJadwalMmpiMessages($created);

        $message = count($created) > 0
            ? count($created) . ' jadwal test MMPI berhasil dibuat.'
            : 'Tidak ada jadwal test MMPI yang dibuat. Kandidat mungkin sudah mendapatkan jadwal MMPI atau data Zoom sudah dihapus.';

        if (count($created) > 0) {
            if (($waResult['total_dikirim'] ?? 0) > 0 && ($waResult['total_gagal_provider'] ?? 0) === 0 && ($waResult['total_dilewati'] ?? 0) === 0) {
                $message .= ' Pesan WhatsApp jadwal MMPI berhasil dikirim melalui OpenWA.';
            } elseif (($waResult['total_dikirim'] ?? 0) > 0) {
                $message .= ' Sebagian pesan WhatsApp jadwal MMPI berhasil dikirim melalui OpenWA. Cek detail wa_result.';
            } else {
                $message .= ' Jadwal tersimpan, tetapi pesan WhatsApp jadwal MMPI belum terkirim. Cek detail wa_result.';
            }
        }

        return response()->json([
            'success' => count($created) > 0,
            'message' => $message,
            'created_count' => count($created),
            'skipped_count' => count($skipped),
            'skipped' => $skipped,
            'wa_result' => $waResult,
        ], count($created) > 0 ? 201 : 422);
    }

    public function destroy(string $id): JsonResponse
    {
        $jadwal = $this->findScopedJadwalMmpiOrFail($id);

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


    private function sendJadwalMmpiMessages($jadwals, ?string $template = null): array
    {
        $jadwals = collect($jadwals)
            ->filter(fn ($item) => $item instanceof JadwalTestMmpi)
            ->values();

        if ($jadwals->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Tidak ada jadwal MMPI untuk dikirim pesan.',
                'total_data' => 0,
                'total_dikirim' => 0,
                'total_dilewati' => 0,
                'total_gagal_provider' => 0,
                'total_perusahaan' => 0,
                'skipped' => [],
                'targets' => [],
                'perusahaan_responses' => [],
                'provider_responses' => [],
            ];
        }

        $ids = $jadwals->pluck('id')->filter()->values()->all();
        $pelamarColumns = $this->getPelamarColumns();

        $rows = DB::table('jadwal_test_mmpi as jtm')
            ->join('data_riwayat_diri as drd', 'drd.id', '=', 'jtm.data_riwayat_diri_id')
            ->leftJoin('data_perusahaan as dp', 'dp.id', '=', 'drd.perusahaan_dilamar')
            ->whereIn('jtm.id', $ids)
            ->whereNull('jtm.deleted_at');

        if (Schema::hasColumn('data_riwayat_diri', 'deleted_at')) {
            $rows->whereNull('drd.deleted_at');
        }

        $rows = $rows
            ->select([
                'jtm.id as jadwal_mmpi_id',
                'jtm.data_riwayat_diri_id',
                'jtm.tanggal',
                'drd.token',
                'drd.perusahaan_dilamar',
                DB::raw($pelamarColumns['nama'] . ' as nama_lengkap'),
                DB::raw($pelamarColumns['no_hp'] . ' as no_wa'),
                'dp.id as perusahaan_id',
                'dp.nama_perusahaan',
                'dp.no_wa as no_wa_perusahaan',
            ])
            ->orderBy('jtm.tanggal')
            ->get();

        $wahaSession = $this->checkOpenWaSessionForSending();
        $wahaDeviceNumber = $this->normalizeWhatsappNumber($wahaSession['device_number'] ?? null);

        $groupedMessages = [];
        $skipped = [];

        foreach ($rows as $row) {
            $target = $this->normalizeWhatsappNumber($row->no_wa ?? null);
            $nomerPerusahaan = $this->normalizeWhatsappNumber($row->no_wa_perusahaan ?? null);

            if (!$target) {
                $skipped[] = [
                    'jadwal_mmpi_id' => $row->jadwal_mmpi_id,
                    'data_riwayat_diri_id' => $row->data_riwayat_diri_id,
                    'nama_lengkap' => $row->nama_lengkap,
                    'no_wa' => $row->no_wa,
                    'perusahaan' => $row->nama_perusahaan,
                    'reason' => 'Nomor WhatsApp kandidat kosong atau tidak valid.',
                ];

                continue;
            }

            if (empty($row->perusahaan_id)) {
                $skipped[] = [
                    'jadwal_mmpi_id' => $row->jadwal_mmpi_id,
                    'data_riwayat_diri_id' => $row->data_riwayat_diri_id,
                    'nama_lengkap' => $row->nama_lengkap,
                    'target' => $target,
                    'reason' => 'Data perusahaan kandidat tidak ditemukan.',
                ];

                continue;
            }

            if (!$nomerPerusahaan) {
                $skipped[] = [
                    'jadwal_mmpi_id' => $row->jadwal_mmpi_id,
                    'data_riwayat_diri_id' => $row->data_riwayat_diri_id,
                    'nama_lengkap' => $row->nama_lengkap,
                    'target' => $target,
                    'perusahaan_id' => $row->perusahaan_id,
                    'perusahaan' => $row->nama_perusahaan,
                    'reason' => 'Nomor WhatsApp perusahaan kosong atau tidak valid.',
                ];

                continue;
            }

            if (!($wahaSession['success'] ?? false)) {
                $skipped[] = [
                    'jadwal_mmpi_id' => $row->jadwal_mmpi_id,
                    'data_riwayat_diri_id' => $row->data_riwayat_diri_id,
                    'nama_lengkap' => $row->nama_lengkap,
                    'target' => $target,
                    'perusahaan_id' => $row->perusahaan_id,
                    'perusahaan' => $row->nama_perusahaan,
                    'nomer_perusahaan' => $nomerPerusahaan,
                    'reason' => $wahaSession['message'] ?? 'Session OpenWA belum connect.',
                    'waha_device' => $wahaDeviceNumber,
                    'waha_status' => $wahaSession['device_status'] ?? null,
                ];

                continue;
            }

            if ($wahaDeviceNumber && $wahaDeviceNumber !== $nomerPerusahaan) {
                $skipped[] = [
                    'jadwal_mmpi_id' => $row->jadwal_mmpi_id,
                    'data_riwayat_diri_id' => $row->data_riwayat_diri_id,
                    'nama_lengkap' => $row->nama_lengkap,
                    'target' => $target,
                    'perusahaan_id' => $row->perusahaan_id,
                    'perusahaan' => $row->nama_perusahaan,
                    'nomer_perusahaan' => $nomerPerusahaan,
                    'reason' => 'Nomor OpenWA yang aktif tidak sesuai dengan nomor WhatsApp perusahaan kandidat. OpenWA aktif: ' . $wahaDeviceNumber . ', nomor perusahaan: ' . $nomerPerusahaan . '.',
                    'waha_device' => $wahaDeviceNumber,
                    'waha_status' => $wahaSession['device_status'] ?? null,
                ];

                continue;
            }

            $groupKey = (string) ($row->perusahaan_id ?: $nomerPerusahaan);

            if (!isset($groupedMessages[$groupKey])) {
                $groupedMessages[$groupKey] = [
                    'perusahaan_id' => $row->perusahaan_id,
                    'perusahaan' => $row->nama_perusahaan,
                    'nomer_perusahaan' => $nomerPerusahaan,
                    'waha_session' => $wahaSession['session'] ?? config('services.waha.session', env('WAHA_SESSION')),
                    'waha_device' => $wahaDeviceNumber,
                    'waha_status' => $wahaSession['device_status'] ?? null,
                    'messages' => [],
                ];
            }

            $groupedMessages[$groupKey]['messages'][] = [
                'jadwal_mmpi_id' => $row->jadwal_mmpi_id,
                'data_riwayat_diri_id' => $row->data_riwayat_diri_id,
                'nama_lengkap' => $row->nama_lengkap,
                'target' => $target,
                'chat_id' => $target . '@c.us',
                'message' => $this->buildPesanJadwalMmpi($row, $template),
            ];
        }

        if (empty($groupedMessages)) {
            return [
                'success' => false,
                'message' => 'Tidak ada kandidat valid untuk dikirim pesan MMPI. Pastikan nomor WA kandidat, nomor WA perusahaan, dan session OpenWA sudah sesuai.',
                'total_data' => $rows->count(),
                'total_dikirim' => 0,
                'total_dilewati' => count($skipped),
                'total_gagal_provider' => 0,
                'total_perusahaan' => 0,
                'skipped' => $skipped,
                'targets' => [],
                'perusahaan_responses' => [],
                'provider_responses' => [],
            ];
        }

        $responses = [];
        $targets = [];
        $totalDikirim = 0;
        $totalGagalProvider = 0;

        foreach ($groupedMessages as $group) {
            $groupSuccess = 0;
            $groupFailed = 0;
            $messageResponses = [];

            foreach ($group['messages'] as $messageItem) {
                try {
                    $sendResult = $this->sendOpenWaText($messageItem['target'], $messageItem['message']);

                    $targets[] = $messageItem['target'];

                    if ($sendResult['success'] ?? false) {
                        $totalDikirim++;
                        $groupSuccess++;
                    } else {
                        $totalGagalProvider++;
                        $groupFailed++;
                    }

                    $messageResponses[] = [
                        'success' => $sendResult['success'] ?? false,
                        'jadwal_mmpi_id' => $messageItem['jadwal_mmpi_id'],
                        'data_riwayat_diri_id' => $messageItem['data_riwayat_diri_id'],
                        'nama_lengkap' => $messageItem['nama_lengkap'],
                        'target' => $messageItem['target'],
                        'chat_id' => $sendResult['chat_id'] ?? $messageItem['chat_id'],
                        'waha_response' => $sendResult['response'] ?? null,
                        'message' => $sendResult['message'] ?? null,
                    ];

                    usleep(500000);
                } catch (\Throwable $e) {
                    $totalGagalProvider++;
                    $groupFailed++;

                    Log::error('Gagal mengirim pesan OpenWA jadwal MMPI', [
                        'message' => $e->getMessage(),
                        'perusahaan_id' => $group['perusahaan_id'],
                        'perusahaan' => $group['perusahaan'],
                        'target' => $messageItem['target'],
                        'jadwal_mmpi_id' => $messageItem['jadwal_mmpi_id'],
                    ]);

                    $messageResponses[] = [
                        'success' => false,
                        'jadwal_mmpi_id' => $messageItem['jadwal_mmpi_id'],
                        'data_riwayat_diri_id' => $messageItem['data_riwayat_diri_id'],
                        'nama_lengkap' => $messageItem['nama_lengkap'],
                        'target' => $messageItem['target'],
                        'chat_id' => $messageItem['chat_id'],
                        'message' => 'Terjadi kesalahan saat mengirim pesan OpenWA: ' . $e->getMessage(),
                    ];
                }
            }

            $responses[] = [
                'success' => $groupSuccess > 0 && $groupFailed === 0,
                'perusahaan_id' => $group['perusahaan_id'],
                'perusahaan' => $group['perusahaan'],
                'nomer_perusahaan' => $group['nomer_perusahaan'],
                'waha_session' => $group['waha_session'],
                'waha_device' => $group['waha_device'],
                'waha_status' => $group['waha_status'],
                'total_data' => count($group['messages']),
                'total_dikirim' => $groupSuccess,
                'total_gagal' => $groupFailed,
                'targets' => collect($group['messages'])->pluck('target')->values(),
                'responses' => $messageResponses,
                'message' => $groupFailed === 0
                    ? 'Pesan jadwal MMPI berhasil dikirim melalui OpenWA untuk perusahaan ini.'
                    : ($groupSuccess > 0
                        ? 'Sebagian pesan jadwal MMPI berhasil dikirim melalui OpenWA untuk perusahaan ini.'
                        : 'Pesan jadwal MMPI gagal dikirim melalui OpenWA untuk perusahaan ini.'),
            ];
        }

        return [
            'success' => $totalDikirim > 0,
            'message' => $totalDikirim > 0
                ? 'Pengiriman WhatsApp jadwal MMPI selesai diproses.'
                : 'Pesan WhatsApp jadwal MMPI gagal dikirim. Cek detail response per perusahaan.',
            'total_data' => $rows->count(),
            'total_dikirim' => $totalDikirim,
            'total_dilewati' => count($skipped),
            'total_gagal_provider' => $totalGagalProvider,
            'total_perusahaan' => count($groupedMessages),
            'skipped' => $skipped,
            'targets' => array_values(array_unique($targets)),
            'perusahaan_responses' => $responses,
            'provider_responses' => $responses,
        ];
    }

    private function buildPesanJadwalMmpi($row, ?string $template = null): string
    {
        $nama = trim((string) ($row->nama_lengkap ?? '')) ?: 'Kandidat';
        $perusahaan = trim((string) ($row->nama_perusahaan ?? '')) ?: '-';
        $tanggal = !empty($row->tanggal)
            ? Carbon::parse($row->tanggal)->translatedFormat('d F Y')
            : '-';
        $urlTahapan = !empty($row->token)
            ? url('/pendaftaran/' . $row->token)
            : '-';

        $message = $template ?: "Halo {nama},\n\n"
            . "Selamat, Anda dinyatakan lolos Test Zoom dan mendapatkan jadwal Test MMPI.\n\n"
            . "Jadwal Test MMPI:\n"
            . "Tanggal: {tanggal_mmpi}\n\n"
            . "Silakan cek status tahapan seleksi melalui link berikut:\n"
            . "{url_tahapan}\n\n"
            . "Mohon hadir/mengikuti test sesuai jadwal yang telah ditentukan.\n\n"
            . "Terima kasih.\n"
            . "Tim Rekrutmen {perusahaan}";

        return strtr($message, [
            '{nama}' => $nama,
            '{nama_lengkap}' => $nama,
            '{perusahaan}' => $perusahaan,
            '{tanggal_mmpi}' => $tanggal,
            '{url_tahapan}' => $urlTahapan,
            '{token}' => (string) ($row->token ?? ''),
        ]);
    }

    private function sendOpenWaText(string $target, string $message): array
    {
        $baseUrl = $this->openWaBaseUrl();

        // Untuk kirim pesan, OpenWA/WAHA di server Anda memakai Session ID UUID.
        // WAHA_SESSION tetap dipakai sebagai nama session untuk pengecekan.
        $sessionId = $this->openWaSendSessionId();

        $chatId = $target . '@c.us';
        $url = $baseUrl . '/sessions/' . urlencode($sessionId) . '/messages/send-text';

        $payload = [
            'chatId' => $chatId,
            'text' => $message,
        ];

        try {
            $response = Http::withoutVerifying()
                ->withHeaders($this->wahaHeaders())
                ->timeout(60)
                ->post($url, $payload);

            $body = $response->body();
            $json = $response->json();

            Log::info('OpenWA send text MMPI response', [
                'url' => $url,
                'session_id_used_for_send' => $sessionId,
                'session_name_env' => $this->openWaSessionName(),
                'session_id_env' => $this->openWaSessionId(),
                'target' => $target,
                'chat_id' => $chatId,
                'payload' => $payload,
                'http_code' => $response->status(),
                'response_json' => $json,
                'response_body' => $body,
            ]);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'chat_id' => $chatId,
                    'target' => $target,
                    'session_id_used_for_send' => $sessionId,
                    'response' => $json ?: $body,
                    'message' => 'Gagal mengirim pesan melalui OpenWA. HTTP Code: '
                        . $response->status()
                        . '. Session yang dipakai: '
                        . $sessionId
                        . '. Response: '
                        . ($body ?: json_encode($json)),
                ];
            }

            return [
                'success' => true,
                'chat_id' => $chatId,
                'target' => $target,
                'session_id_used_for_send' => $sessionId,
                'response' => $json ?: $body,
                'message' => 'Pesan berhasil dikirim melalui OpenWA.',
            ];
        } catch (\Throwable $e) {
            Log::error('OpenWA send text MMPI exception', [
                'url' => $url,
                'session_id_used_for_send' => $sessionId,
                'target' => $target,
                'chat_id' => $chatId,
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'chat_id' => $chatId,
                'target' => $target,
                'session_id_used_for_send' => $sessionId,
                'response' => null,
                'message' => 'Gagal mengirim pesan melalui OpenWA: ' . $e->getMessage(),
            ];
        }
    }

    private function checkOpenWaSessionForSending(): array
    {
        $baseUrl = $this->openWaBaseUrl();
        $sessionName = $this->openWaSessionName();
        $sessionId = $this->openWaSessionId();
        $sendSessionId = $this->openWaSendSessionId();

        try {
            // Pakai /sessions agar bisa cocokkan berdasarkan name atau UUID.
            // Beberapa versi WAHA/OpenWA tidak stabil jika langsung /sessions/{name}.
            $url = $baseUrl . '/sessions';

            $response = Http::withoutVerifying()
                ->withHeaders($this->wahaHeaders())
                ->timeout(30)
                ->get($url);

            $body = $response->body();
            $json = $response->json();

            Log::info('OpenWA session check MMPI', [
                'url' => $url,
                'session_name_env' => $sessionName,
                'session_id_env' => $sessionId,
                'send_session_id' => $sendSessionId,
                'http_code' => $response->status(),
                'response_json' => $json,
                'response_body' => $body,
            ]);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'session' => $sessionName,
                    'session_id' => $sessionId,
                    'send_session_id' => $sendSessionId,
                    'status' => 'error',
                    'message' => 'Gagal mengecek session OpenWA. HTTP Code: ' . $response->status() . '. Response: ' . $body,
                    'device_number' => null,
                    'device_status' => null,
                    'waha_response' => $json ?: $body,
                ];
            }

            $sessionData = $this->extractOpenWaSessionData($json, $sessionName, $sessionId);

            if (empty($sessionData)) {
                return [
                    'success' => false,
                    'session' => $sessionName,
                    'session_id' => $sessionId,
                    'send_session_id' => $sendSessionId,
                    'status' => 'not_found',
                    'message' => 'Session OpenWA tidak ditemukan. Pastikan WAHA_SESSION berisi nama session dan WAHA_SESSION_ID berisi UUID session.',
                    'device_number' => null,
                    'device_status' => null,
                    'waha_response' => $json,
                ];
            }

            $deviceStatus = strtolower((string) (
                $sessionData['status']
                ?? $sessionData['device_status']
                ?? $sessionData['state']
                ?? $sessionData['engine']['state']
                ?? ''
            ));

            $deviceNumber = $this->extractOpenWaPhoneNumber($sessionData);

            $isConnected = in_array($deviceStatus, [
                'connected',
                'connect',
                'working',
                'authenticated',
                'ready',
            ], true);

            if (!$isConnected) {
                return [
                    'success' => false,
                    'session' => $sessionName,
                    'session_id' => $sessionId,
                    'send_session_id' => $sendSessionId,
                    'status' => 'disconnected',
                    'message' => 'Session OpenWA belum connect. Status saat ini: ' . ($deviceStatus ?: '-'),
                    'device_number' => $deviceNumber,
                    'device_status' => $deviceStatus ?: null,
                    'waha_response' => $json,
                ];
            }

            return [
                'success' => true,
                'session' => $sessionName,
                'session_id' => $sessionId,
                'send_session_id' => $sendSessionId,
                'status' => 'connected',
                'message' => 'Session OpenWA sudah connect.',
                'device_number' => $deviceNumber,
                'device_status' => $deviceStatus,
                'waha_response' => $json,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'session' => $sessionName,
                'session_id' => $sessionId,
                'send_session_id' => $sendSessionId,
                'status' => 'error',
                'message' => 'Gagal memvalidasi OpenWA: ' . $e->getMessage(),
                'device_number' => null,
                'device_status' => null,
            ];
        }
    }

    private function openWaBaseUrl(): string
    {
        $url = rtrim(config('services.waha.url', env('WAHA_URL', 'https://wa.blast.dsicorp.id')), '/');

        // WAHA_URL boleh diisi domain saja atau domain + /api.
        // Hasil akhir dijaga agar hanya memiliki satu /api.
        if (!Str::endsWith($url, '/api')) {
            $url .= '/api';
        }

        return $url;
    }

    private function openWaSessionName(): string
    {
        return (string) (config('services.waha.session', env('WAHA_SESSION', 'rekruitment')) ?: 'rekruitment');
    }

    private function openWaSessionId(): ?string
    {
        $sessionId = config('services.waha.session_id', env('WAHA_SESSION_ID'));

        return $sessionId ? (string) $sessionId : null;
    }

    private function openWaSendSessionId(): string
    {
        // Untuk endpoint kirim pesan, gunakan UUID jika tersedia.
        return $this->openWaSessionId() ?: $this->openWaSessionName();
    }

    private function wahaHeaders(): array
    {
        $apiKey = config('services.waha.api_key') ?: env('WAHA_API_KEY');

        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        if (!empty($apiKey)) {
            // Gunakan format header yang sama dengan controller WAHA lain.
            $headers['X-Api-Key'] = $apiKey;
        }

        return $headers;
    }

    private function extractOpenWaSessionData($json, string $sessionName, ?string $sessionId = null): array
    {
        if (is_array($json) && array_is_list($json)) {
            foreach ($json as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $itemName = $item['name'] ?? null;
                $itemSession = $item['session'] ?? null;
                $itemId = $item['id'] ?? null;
                $itemSessionId = $item['sessionId'] ?? null;
                $itemUuid = $item['uuid'] ?? null;

                if (
                    $itemName === $sessionName ||
                    $itemSession === $sessionName ||
                    ($sessionId && $itemId === $sessionId) ||
                    ($sessionId && $itemSessionId === $sessionId) ||
                    ($sessionId && $itemUuid === $sessionId)
                ) {
                    return $item;
                }
            }

            return [];
        }

        return is_array($json) ? $json : [];
    }

    private function extractOpenWaPhoneNumber(array $sessionData): ?string
    {
        $phone = $sessionData['phone'] ?? null;

        if (is_array($phone)) {
            $candidates = [
                $phone['number'] ?? null,
                $phone['phone'] ?? null,
                $phone['user'] ?? null,
                $phone['id'] ?? null,
            ];
        } else {
            $candidates = [$phone];
        }

        $candidates = array_merge($candidates, [
            $sessionData['phoneNumber'] ?? null,
            $sessionData['phone_number'] ?? null,
            $sessionData['me']['id'] ?? null,
            $sessionData['me']['user'] ?? null,
            $sessionData['me']['number'] ?? null,
            $sessionData['me']['phone'] ?? null,
        ]);

        foreach ($candidates as $candidate) {
            if (is_array($candidate)) {
                $candidate = $candidate['number']
                    ?? $candidate['id']
                    ?? $candidate['user']
                    ?? $candidate['phone']
                    ?? null;
            }

            $number = $this->normalizeWhatsappNumber($candidate);

            if ($number) {
                return $number;
            }
        }

        return null;
    }

    private function normalizeWhatsappNumber($value): ?string
    {
        $number = preg_replace('/\D+/', '', (string) ($value ?? ''));

        if ($number === '') {
            return null;
        }

        if (str_starts_with($number, '0')) {
            $number = '62' . substr($number, 1);
        }

        if (str_starts_with($number, '8')) {
            $number = '62' . $number;
        }

        if (strlen($number) < 10 || strlen($number) > 16) {
            return null;
        }

        return $number;
    }

    private function applyCompanyScopeToDrdJoin($query, string $pelamarAlias = 'drd'): void
    {
        $allowedPerusahaanIds = $this->currentUserPerusahaanIds();

        if (is_array($allowedPerusahaanIds)) {
            if (empty($allowedPerusahaanIds)) {
                $query->whereRaw('1 = 0');
                return;
            }

            $query->whereIn($pelamarAlias . '.perusahaan_dilamar', $allowedPerusahaanIds);
        }
    }

    private function findScopedJadwalMmpiOrFail(string $id): JadwalTestMmpi
    {
        $query = JadwalTestMmpi::query()
            ->where('id', $id);

        $allowedPerusahaanIds = $this->currentUserPerusahaanIds();

        if (is_array($allowedPerusahaanIds)) {
            if (empty($allowedPerusahaanIds)) {
                abort(404);
            }

            $query->whereExists(function ($subQuery) use ($allowedPerusahaanIds) {
                $subQuery
                    ->select(DB::raw(1))
                    ->from('data_riwayat_diri as drd')
                    ->whereColumn('drd.id', 'jadwal_test_mmpi.data_riwayat_diri_id')
                    ->whereIn('drd.perusahaan_dilamar', $allowedPerusahaanIds);

                if (Schema::hasColumn('data_riwayat_diri', 'deleted_at')) {
                    $subQuery->whereNull('drd.deleted_at');
                }
            });
        }

        return $query->firstOrFail();
    }

    /**
     * Return:
     * - null  => user boleh akses semua perusahaan, contoh Superadmin.
     * - array => user hanya boleh akses perusahaan tertentu.
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

        /**
         * Fallback kalau masih ada sistem lama:
         * users.perusahaan_id
         */
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