<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agama;
use App\Models\DataPerusahaan;
use App\Models\DataRiwayatDiri;
use App\Models\Kewarganegaraan;
use App\Models\Pendidikan;
use App\Models\Posisi;
use App\Models\SumberInformasi;
use App\Models\StatusPernikahan;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DataPelamarController extends Controller
{
    /**
     * WA tidak bisa membuka link 127.0.0.1 / localhost.
     * Isi dengan domain publik aplikasi Anda, contoh:
     * private ?string $publicBaseUrl = 'https://rekrutmen.domainanda.com';
     * atau saat testing pakai ngrok:
     * private ?string $publicBaseUrl = 'https://xxxx.ngrok-free.app';
     */
    private ?string $publicBaseUrl = null;

    private array $relations = [
        'pendidikan',
        'agama',
        'kewarganegaraan',
        'statusPernikahan',
        'posisi',
        'perusahaan',
        'creator:uuid,name,email',
        'sosialMedia',
        'sumberInformasi',
        'riwayatKeluarga',
        'saudaraKandung',
        'saudaraIpar',
        'riwayatKesehatan',
        'riwayatKesehatan.opsiKacamata',
        'riwayatPekerjaan',
        'kesiapanBekerja',
    ];

    private array $completionSteps = [
        [
            'key' => 'data_diri',
            'label' => 'Data Diri',
            'description' => 'Identitas utama dan informasi lamaran.',
            'order' => 1,
            'percentage' => 20,
            'targets' => [
                [
                    'relation' => null,
                    'fields' => [
                        'posisi_yang_dilamar',
                        'posisi_dilamar',
                        'perusahaan_dilamar',
                        'sumber_informasi_id',
                        'nama_lengkap',
                        'nama_panggil',
                        'email',
                        'no_wa',
                        'pendidikan_id',
                        'jurusan',
                        'nama_institusi',
                        'agama_id',
                        'tanggal_lahir',
                        'tanggal_skrining',
                        'tempat_lahir',
                        'jenis_kelamin',
                        'alamat_ktp',
                        'alamat_domisili',
                        'alamat',
                        'provinsi_id',
                        'kabupaten_id',
                        'kecamatan_id',
                        'kelurahan_id',
                        'kewarganegaraan_id',
                        'status_pernikahan_id',
                        'str_aktif',
                    ],
                ],
                [
                    'relation' => 'sosialMedia',
                    'fields' => [
                        'platform',
                        'nama_account',
                        'nama_akun',
                    ],
                ],
            ],
        ],
        [
            'key' => 'riwayat_keluarga',
            'label' => 'Riwayat Keluarga',
            'description' => 'Data keluarga dan kontak darurat.',
            'order' => 2,
            'percentage' => 40,
            'targets' => [
                [
                    'relation' => 'riwayatKeluarga',
                    'fields' => [
                        'nama_ayah_kandung',
                        'pekerjaan_ayah_kandung',
                        'nama_ibu_kandung',
                        'pekerjaan_ibu_kandung',
                        'nama_ayah',
                        'nik_ayah',
                        'tempat_lahir_ayah',
                        'tanggal_lahir_ayah',
                        'pekerjaan_ayah',
                        'no_hp_ayah',
                        'alamat_ayah',
                        'nama_ibu',
                        'nik_ibu',
                        'tempat_lahir_ibu',
                        'tanggal_lahir_ibu',
                        'pekerjaan_ibu',
                        'no_hp_ibu',
                        'alamat_ibu',
                        'nama_suami_istri',
                        'pekerjaan_suami_istri',
                        'pekerjaan_sumi_istri',
                        'tlpn_suami_istri',
                        'nama_bapak_mertua',
                        'pekerjaan_bapak_mertua',
                        'nama_ibu_mertua',
                        'pekerjaan_ibu_mertua',
                        'kerabat_bekerja_diinstansi',
                        'hubungan_kerabat_instansi',
                        'kontak_darurat',
                        'tlpn_darurat',
                    ],
                ],
                [
                    'relation' => 'saudaraKandung',
                    'fields' => [
                        'nama_saudara_kandung',
                        'nama',
                        'pekerjaan',
                        'jenis_kelamin',
                        'hubungan',
                        'no_hp',
                        'alamat',
                    ],
                ],
                [
                    'relation' => 'saudaraIpar',
                    'fields' => [
                        'nama_saudara_ipar',
                        'nama',
                        'pekerjaan',
                        'jenis_kelamin',
                        'hubungan',
                        'no_hp',
                        'alamat',
                    ],
                ],
            ],
        ],
        [
            'key' => 'riwayat_kesehatan',
            'label' => 'Riwayat Kesehatan',
            'description' => 'Informasi kesehatan pelamar.',
            'order' => 3,
            'percentage' => 60,
            'targets' => [
                [
                    'relation' => 'riwayatKesehatan',
                    'fields' => [
                        'buta_warna',
                        'opsi_kacamata_id',
                        'alat_bantu_dengar',
                        'menulis_dengan_tangan',
                        'sering_gemetar',
                        'tangan_sering_berkeringat',
                        'penyakit_menular',
                        'program_kehamilan',
                        'punya_alergi',
                        'nama_alergi',
                        'punya_penyakit_genetik',
                        'nama_penyakit',
                        'riwayat_kronis',
                        'pengobatan_psikolog',
                        'kapan_dilakukan',
                        'pernah_kecelakaan',
                        'bagian_tubuh_kecelakaan',
                        'pernah_operasi',
                        'diagnosa_dokter',
                    ],
                ],
                [
                    'relation' => null,
                    'fields' => [
                        'gol_darah',
                        'golongan_darah',
                        'tinggi_badan',
                        'berat_badan',
                    ],
                ],
            ],
        ],
        [
            'key' => 'riwayat_pekerjaan',
            'label' => 'Riwayat Pekerjaan',
            'description' => 'Pengalaman kerja dan keahlian.',
            'order' => 4,
            'percentage' => 80,
            'targets' => [
                [
                    'relation' => 'riwayatPekerjaan',
                    'fields' => [
                        'nama_perusahaan',
                        'posisi_pekerjaan_terakhir',
                        'periode_kerja_awal',
                        'periode_kerja_akhir',
                        'gaji_terakhir',
                        'referensi_kerja',
                        'refrensi_kerja',
                        'nama_refrensi',
                        'telp_refrensi',
                        'refrensi_rekan_kerja',
                        'nama_refrensi_rekan',
                        'telp_refrensi_rekan',
                        'refrensi_kerabat',
                        'nama_refrensi_kerabat',
                        'telp_refrensi_kerabat',
                        'status_pekerjaan',
                        'posisi_pekerjaan',
                        'bidang_pekerjaan',
                        'lokasi_perusahaan',
                        'tahun_mulai_bekerja',
                        'tahun_selesai_bekerja',
                        'lama_bekerja',
                        'deskripsi_pekerjaan',
                        'alasan_berhenti',
                        'keahlian',
                        'catatan_pekerjaan',
                    ],
                ],
            ],
        ],
        [
            'key' => 'kesiapan_bekerja',
            'label' => 'Kesiapan Bekerja',
            'description' => 'Kesiapan penempatan dan mulai kerja.',
            'order' => 5,
            'percentage' => 100,
            'targets' => [
                [
                    'relation' => 'kesiapanBekerja',
                    'fields' => [
                        'kapan_siap_bekerja',
                        'tanggal_siap_kerja',
                        'ekpetasi_gaji',
                        'ekptasi_gaji',
                        'gaji_diharapkan',
                        'penempatan',
                        'penempatan_luar_jawa_tengah',
                        'proses_bkhang',
                        'proses_bhaking',
                        'background_checking',
                        'dapat_dipertanggung_jawabkan',
                        'pernyataan_data_benar',
                        'bersedia_training',
                        'bersedia_pelatihan',
                    ],
                ],
            ],
        ],
    ];

    public function index()
    {
        return view('pages.admin.data-pelamar.index', [
            'title' => 'Data Pelamar',
        ]);
    }

    public function list(Request $request): JsonResponse
    {
        $allowedPerusahaanIds = $this->currentUserPerusahaanIds();

        $perusahaanRules = [
            'nullable',
            'uuid',
            Rule::exists('data_perusahaan', 'id'),
        ];

        if (is_array($allowedPerusahaanIds)) {
            $perusahaanRules[] = Rule::in($allowedPerusahaanIds);
        }

        $validated = $request->validate([
            'tanggal_skrining_mulai' => ['nullable', 'date'],
            'tanggal_skrining_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_skrining_mulai'],
            'perusahaan_dilamar' => $perusahaanRules,
        ], [
            'perusahaan_dilamar.uuid' => 'Perusahaan tidak valid.',
            'perusahaan_dilamar.exists' => 'Perusahaan tidak ditemukan.',
            'perusahaan_dilamar.in' => 'Perusahaan tidak sesuai dengan perusahaan akun login.',
        ]);

        $query = $this->scopedPelamarQuery()
            ->with($this->safeRelations());

        if (!empty($validated['perusahaan_dilamar'])) {
            $query->where('perusahaan_dilamar', $validated['perusahaan_dilamar']);
        }

        if (!empty($validated['tanggal_skrining_mulai'])) {
            $query->whereDate('tanggal_skrining', '>=', $validated['tanggal_skrining_mulai']);
        }

        if (!empty($validated['tanggal_skrining_selesai'])) {
            $query->whereDate('tanggal_skrining', '<=', $validated['tanggal_skrining_selesai']);
        }

        $data = $query
            ->latest()
            ->get()
            ->map(function ($item) {
                return $this->appendExtraData($item);
            });

        return response()->json([
            'success' => true,
            'message' => 'Data pelamar berhasil diambil.',
            'filter' => [
                'perusahaan_dilamar' => $validated['perusahaan_dilamar'] ?? null,
                'tanggal_skrining_mulai' => $validated['tanggal_skrining_mulai'] ?? null,
                'tanggal_skrining_selesai' => $validated['tanggal_skrining_selesai'] ?? null,
            ],
            'data' => $data,
        ]);
    }


    public function kirimPesanSkrining(Request $request): JsonResponse
    {
        $allowedPerusahaanIds = $this->currentUserPerusahaanIds();

        $perusahaanRules = [
            'nullable',
            'uuid',
            Rule::exists('data_perusahaan', 'id'),
        ];

        if (is_array($allowedPerusahaanIds)) {
            $perusahaanRules[] = Rule::in($allowedPerusahaanIds);
        }

        $validated = $request->validate([
            'tanggal_skrining_mulai' => ['required', 'date'],
            'tanggal_skrining_selesai' => ['required', 'date', 'after_or_equal:tanggal_skrining_mulai'],
            'perusahaan_dilamar' => $perusahaanRules,
            'pelamar_ids' => ['required', 'array', 'min:1'],
            'pelamar_ids.*' => ['required', 'string'],
            'message_template' => ['nullable', 'string', 'max:5000'],
        ], [
            'tanggal_skrining_mulai.required' => 'Tanggal skrining mulai wajib diisi.',
            'tanggal_skrining_selesai.required' => 'Tanggal skrining selesai wajib diisi.',
            'tanggal_skrining_selesai.after_or_equal' => 'Tanggal skrining selesai tidak boleh lebih kecil dari tanggal mulai.',
            'pelamar_ids.required' => 'Pilih minimal 1 kandidat yang akan dikirim pesan.',
            'pelamar_ids.array' => 'Format kandidat yang dipilih tidak valid.',
            'pelamar_ids.min' => 'Pilih minimal 1 kandidat yang akan dikirim pesan.',
            'perusahaan_dilamar.uuid' => 'Perusahaan tidak valid.',
            'perusahaan_dilamar.exists' => 'Perusahaan tidak ditemukan.',
            'perusahaan_dilamar.in' => 'Perusahaan tidak sesuai dengan perusahaan akun login.',
        ]);

        $selectedIds = collect($validated['pelamar_ids'] ?? [])
            ->map(fn ($id) => trim((string) $id))
            ->filter()
            ->unique()
            ->values();

        if ($selectedIds->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Pilih minimal 1 kandidat yang akan dikirim pesan.',
                'total_data' => 0,
                'total_dikirim' => 0,
                'total_dilewati' => 0,
                'total_gagal_provider' => 0,
                'skipped' => [],
                'perusahaan_responses' => [],
            ], 422);
        }

        $query = $this->scopedPelamarQuery()
            ->with($this->safeRelations())
            ->whereIn('id', $selectedIds->all())
            ->whereDate('tanggal_skrining', '>=', $validated['tanggal_skrining_mulai'])
            ->whereDate('tanggal_skrining', '<=', $validated['tanggal_skrining_selesai']);

        if (!empty($validated['perusahaan_dilamar'])) {
            $query->where('perusahaan_dilamar', $validated['perusahaan_dilamar']);
        }

        $pelamars = $query
            ->orderBy('tanggal_skrining', 'asc')
            ->orderBy('nama_lengkap', 'asc')
            ->get()
            ->map(function ($item) {
                return $this->appendExtraData($item);
            });

        if ($pelamars->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada kandidat terpilih yang sesuai dengan filter tanggal/perusahaan tersebut.',
                'tanggal_skrining_mulai' => $validated['tanggal_skrining_mulai'],
                'tanggal_skrining_selesai' => $validated['tanggal_skrining_selesai'],
                'perusahaan_dilamar' => $validated['perusahaan_dilamar'] ?? null,
                'total_pelamar' => 0,
                'total_data' => 0,
                'total_dipilih' => $selectedIds->count(),
                'total_dikirim' => 0,
                'total_dilewati' => 0,
                'total_gagal_provider' => 0,
                'total_perusahaan' => 0,
                'skipped' => [],
                'targets' => [],
                'perusahaan_responses' => [],
            ], 404);
        }

        $wahaSession = $this->checkWahaSessionForSending();
        $wahaDeviceNumber = $this->normalizeWhatsappNumber($wahaSession['device_number'] ?? null);

        $groupedMessages = [];
        $skipped = [];

        foreach ($pelamars as $pelamar) {
            $target = $this->normalizeWhatsappNumber($pelamar->no_wa ?? null);
            $urlPendaftaran = $pelamar->pendaftaran_url ?: $this->makePendaftaranUrl($pelamar->token ?? null);
            $perusahaan = $pelamar->perusahaan;
            $nomerPerusahaan = $this->normalizeWhatsappNumber($perusahaan->no_wa ?? null);

            if (!$target) {
                $skipped[] = [
                    'id' => $this->pelamarKey($pelamar),
                    'nama_lengkap' => $pelamar->nama_lengkap,
                    'no_wa' => $pelamar->no_wa,
                    'perusahaan' => $pelamar->perusahaan_label,
                    'pendaftaran_url' => $urlPendaftaran,
                    'reason' => 'Nomor WhatsApp kandidat kosong atau tidak valid.',
                ];

                continue;
            }

            if (!$urlPendaftaran) {
                $skipped[] = [
                    'id' => $this->pelamarKey($pelamar),
                    'nama_lengkap' => $pelamar->nama_lengkap,
                    'no_wa' => $pelamar->no_wa,
                    'target' => $target,
                    'perusahaan' => $pelamar->perusahaan_label,
                    'pendaftaran_url' => null,
                    'reason' => 'URL pendaftaran tidak tersedia karena token kandidat kosong.',
                ];

                continue;
            }

            if (!$perusahaan) {
                $skipped[] = [
                    'id' => $this->pelamarKey($pelamar),
                    'nama_lengkap' => $pelamar->nama_lengkap,
                    'no_wa' => $pelamar->no_wa,
                    'target' => $target,
                    'perusahaan' => $pelamar->perusahaan_label,
                    'pendaftaran_url' => $urlPendaftaran,
                    'reason' => 'Data perusahaan kandidat tidak ditemukan.',
                ];

                continue;
            }

            if (!$nomerPerusahaan) {
                $skipped[] = [
                    'id' => $this->pelamarKey($pelamar),
                    'nama_lengkap' => $pelamar->nama_lengkap,
                    'no_wa' => $pelamar->no_wa,
                    'target' => $target,
                    'perusahaan_id' => $perusahaan->id ?? null,
                    'perusahaan' => $perusahaan->nama_perusahaan ?? $pelamar->perusahaan_label,
                    'pendaftaran_url' => $urlPendaftaran,
                    'reason' => 'Nomor WhatsApp perusahaan kosong atau tidak valid.',
                ];

                continue;
            }

            if (!($wahaSession['success'] ?? false)) {
                $skipped[] = [
                    'id' => $this->pelamarKey($pelamar),
                    'nama_lengkap' => $pelamar->nama_lengkap,
                    'no_wa' => $pelamar->no_wa,
                    'target' => $target,
                    'perusahaan_id' => $perusahaan->id ?? null,
                    'perusahaan' => $perusahaan->nama_perusahaan ?? $pelamar->perusahaan_label,
                    'nomer_perusahaan' => $nomerPerusahaan,
                    'pendaftaran_url' => $urlPendaftaran,
                    'reason' => $wahaSession['message'] ?? 'Session WAHA belum connect.',
                    'waha_device' => $wahaDeviceNumber,
                    'waha_status' => $wahaSession['device_status'] ?? null,
                ];

                continue;
            }

            if ($wahaDeviceNumber && $wahaDeviceNumber !== $nomerPerusahaan) {
                $skipped[] = [
                    'id' => $this->pelamarKey($pelamar),
                    'nama_lengkap' => $pelamar->nama_lengkap,
                    'no_wa' => $pelamar->no_wa,
                    'target' => $target,
                    'perusahaan_id' => $perusahaan->id ?? null,
                    'perusahaan' => $perusahaan->nama_perusahaan ?? $pelamar->perusahaan_label,
                    'nomer_perusahaan' => $nomerPerusahaan,
                    'pendaftaran_url' => $urlPendaftaran,
                    'reason' => 'Nomor WAHA yang aktif tidak sesuai dengan nomor WhatsApp perusahaan kandidat. WAHA aktif: ' . $wahaDeviceNumber . ', nomor perusahaan: ' . $nomerPerusahaan . '.',
                    'waha_device' => $wahaDeviceNumber,
                    'waha_status' => $wahaSession['device_status'] ?? null,
                ];

                continue;
            }

            $groupKey = (string) ($perusahaan->id ?? $nomerPerusahaan);

            if (!isset($groupedMessages[$groupKey])) {
                $groupedMessages[$groupKey] = [
                    'perusahaan_id' => $perusahaan->id ?? null,
                    'perusahaan' => $perusahaan->nama_perusahaan ?? $pelamar->perusahaan_label,
                    'nomer_perusahaan' => $nomerPerusahaan,
                    'waha_session' => $wahaSession['session'] ?? config('services.waha.session', env('WAHA_SESSION', 'rekruitment')),
                    'waha_device' => $wahaDeviceNumber,
                    'waha_status' => $wahaSession['device_status'] ?? null,
                    'messages' => [],
                ];
            }

            $groupedMessages[$groupKey]['messages'][] = [
                'pelamar_id' => $this->pelamarKey($pelamar),
                'nama_lengkap' => $pelamar->nama_lengkap,
                'target' => $target,
                'chat_id' => $target . '@c.us',
                'message' => $this->buildPesanSkrining(
                    $pelamar,
                    $validated['message_template'] ?? null
                ),
            ];
        }

        if (empty($groupedMessages)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada kandidat valid untuk dikirim pesan. Pastikan nomor WA kandidat, token pendaftaran, nomor WA perusahaan, dan session WAHA sudah sesuai.',
                'tanggal_skrining_mulai' => $validated['tanggal_skrining_mulai'],
                'tanggal_skrining_selesai' => $validated['tanggal_skrining_selesai'],
                'perusahaan_dilamar' => $validated['perusahaan_dilamar'] ?? null,
                'total_pelamar' => $pelamars->count(),
                'total_data' => $pelamars->count(),
                'total_dipilih' => $selectedIds->count(),
                'total_dikirim' => 0,
                'total_dilewati' => count($skipped),
                'total_gagal_provider' => 0,
                'total_perusahaan' => 0,
                'skipped' => $skipped,
                'targets' => [],
                'perusahaan_responses' => [],
                'provider_responses' => [],
            ], 422);
        }

        $responses = [];
        $totalDikirim = 0;
        $totalGagalProvider = 0;
        $targets = [];

        foreach ($groupedMessages as $group) {
            $groupSuccess = 0;
            $groupFailed = 0;
            $messageResponses = [];

            foreach ($group['messages'] as $messageItem) {
                try {
                    $sendResult = $this->sendWahaText(
                        $messageItem['target'],
                        $messageItem['message']
                    );

                    $targets[] = $messageItem['target'];

                    if ($sendResult['success']) {
                        $totalDikirim++;
                        $groupSuccess++;
                    } else {
                        $totalGagalProvider++;
                        $groupFailed++;
                    }

                    $messageResponses[] = [
                        'success' => $sendResult['success'],
                        'pelamar_id' => $messageItem['pelamar_id'],
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

                    Log::error('Gagal mengirim pesan WAHA skrining', [
                        'message' => $e->getMessage(),
                        'perusahaan_id' => $group['perusahaan_id'],
                        'perusahaan' => $group['perusahaan'],
                        'target' => $messageItem['target'],
                        'pelamar_id' => $messageItem['pelamar_id'],
                    ]);

                    $messageResponses[] = [
                        'success' => false,
                        'pelamar_id' => $messageItem['pelamar_id'],
                        'nama_lengkap' => $messageItem['nama_lengkap'],
                        'target' => $messageItem['target'],
                        'chat_id' => $messageItem['chat_id'],
                        'message' => 'Terjadi kesalahan saat mengirim pesan WAHA: ' . $e->getMessage(),
                    ];
                }
            }

            $firstFailedMessage = collect($messageResponses)
                ->where('success', false)
                ->pluck('message')
                ->filter()
                ->first();

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
                    ? 'Pesan berhasil dikirim melalui WAHA untuk perusahaan ini.'
                    : ($firstFailedMessage ?: 'Pesan gagal dikirim melalui WAHA untuk perusahaan ini.'),
            ];
        }

        $isAllSuccess = $totalDikirim > 0 && $totalGagalProvider === 0;
        $isPartialSuccess = $totalDikirim > 0 && $totalGagalProvider > 0;

        return response()->json([
            'success' => $totalDikirim > 0,
            'message' => $isAllSuccess
                ? 'Pesan WhatsApp berhasil dikirim sesuai perusahaan kandidat.'
                : ($isPartialSuccess
                    ? 'Sebagian pesan WhatsApp berhasil dikirim, sebagian gagal/dilewati. Cek detail response per perusahaan.'
                    : 'Pesan WhatsApp gagal dikirim. Cek detail response per perusahaan.'),
            'tanggal_skrining_mulai' => $validated['tanggal_skrining_mulai'],
            'tanggal_skrining_selesai' => $validated['tanggal_skrining_selesai'],
            'perusahaan_dilamar' => $validated['perusahaan_dilamar'] ?? null,
            'total_pelamar' => $pelamars->count(),
            'total_data' => $pelamars->count(),
            'total_dipilih' => $selectedIds->count(),
            'total_dikirim' => $totalDikirim,
            'total_dilewati' => count($skipped),
            'total_gagal_provider' => $totalGagalProvider,
            'total_perusahaan' => count($groupedMessages),
            'skipped' => $skipped,
            'targets' => array_values(array_unique($targets)),
            'perusahaan_responses' => $responses,
            'provider_responses' => $responses,
        ], $totalDikirim > 0 ? 200 : 422);
    }

    public function posisiList(): JsonResponse
    {
        $data = Posisi::query()
            ->orderBy('nama_posisi')
            ->get([
                'id',
                'nama_posisi',
                'str_aktif',
            ]);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function perusahaanList(): JsonResponse
    {
        $query = DataPerusahaan::query()
            ->orderBy('nama_perusahaan');

        $allowedPerusahaanIds = $this->currentUserPerusahaanIds();

        if (is_array($allowedPerusahaanIds)) {
            $query->whereIn('id', $allowedPerusahaanIds);
        }

        $data = $query->get([
            'id',
            'kode',
            'nama_perusahaan',
            'no_wa',
            'token_api_wa',
        ]);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }


    public function sumberInformasiList(): JsonResponse
    {
        $data = SumberInformasi::query()
            ->orderBy('informasi')
            ->get([
                'id',
                'informasi',
            ]);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function pendidikanList(): JsonResponse
    {
        $data = Pendidikan::query()
            ->orderBy('pendidikan')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function agamaList(): JsonResponse
    {
        $data = Agama::query()
            ->orderBy('agama')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function kewarganegaraanList(): JsonResponse
    {
        $data = Kewarganegaraan::query()
            ->orderBy('kewarganegaraan')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function statusPernikahanList(): JsonResponse
    {
        $data = StatusPernikahan::query()
            ->orderBy('status_pernikahan')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validatePelamar($request);

        /**
         * Simpan user pembuat data.
         * Kolom created_by wajib sudah ada di tabel data_riwayat_diri.
         */
        if (Schema::hasColumn('data_riwayat_diri', 'created_by')) {
            $validated['created_by'] = Auth::user()?->uuid;
        }

        $data = DataRiwayatDiri::query()->create($validated);

        $freshData = $data->fresh($this->safeRelations());
        $freshData = $this->appendExtraData($freshData);

        return response()->json([
            'success' => true,
            'message' => 'Data pelamar berhasil disimpan.',
            'data' => $freshData,
            'token' => $freshData->token,
            'pendaftaran_url' => $freshData->pendaftaran_url,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $data = $this->findScopedPelamarOrFail($id, true);

        $data = $this->appendExtraData($data);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }


    public function detail(string $id)
    {
        return view('pages.admin.data-pelamar.detail', [
            'title' => 'Detail Data Pelamar',
            'id' => $id,
        ]);
    }

    public function detailData(string $id): JsonResponse
    {
        $data = $this->findScopedPelamarOrFail($id, true);

        $data = $this->appendExtraData($data);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }


    public function showByToken(string $token)
    {
        $data = DataRiwayatDiri::query()
            ->with($this->safeRelations())
            ->where('token', $token)
            ->firstOrFail();

        $data = $this->appendExtraData($data);

        return view('pages.pendaftaran.index', [
            'title' => 'Pendaftaran',
            'token' => $token,
            'pelamar' => $data,
        ]);
    }

    public function findByToken(string $token): JsonResponse
    {
        $pelamar = DataRiwayatDiri::query()
            ->with($this->safeRelations())
            ->where('token', $token)
            ->first();

        if (!$pelamar) {
            return response()->json([
                'success' => false,
                'message' => 'Token pelamar tidak ditemukan.',
                'data' => null,
            ], 404);
        }

        $pelamar = $this->appendExtraData($pelamar);

        return response()->json([
            'success' => true,
            'message' => 'Data pelamar ditemukan.',
            'data' => $pelamar,
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $data = $this->findScopedPelamarOrFail($id);

        $validated = $this->validatePelamar($request, true);

        if (!$request->has('str_aktif')) {
            unset($validated['str_aktif']);
        }

        $data->update($validated);

        $freshData = $data->fresh($this->safeRelations());
        $freshData = $this->appendExtraData($freshData);

        return response()->json([
            'success' => true,
            'message' => 'Data pelamar berhasil diperbarui.',
            'data' => $freshData,
        ]);
    }


    public function destroy(string $id): JsonResponse
    {
        $data = $this->findScopedPelamarOrFail($id);

        try {
            $data->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data pelamar berhasil dihapus.',
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data pelamar tidak bisa dihapus karena masih digunakan oleh data lain.',
            ], 409);
        }
    }


    private function validatePelamar(Request $request, bool $isUpdate = false): array
    {
        $allowedPerusahaanIds = $this->currentUserPerusahaanIds();

        $perusahaanRules = [
            'required',
            'uuid',
            Rule::exists('data_perusahaan', 'id'),
        ];

        if (is_array($allowedPerusahaanIds)) {
            $perusahaanRules[] = Rule::in($allowedPerusahaanIds);
        }

        return $request->validate([
            'posisi_yang_dilamar' => [
                'required',
                'uuid',
                Rule::exists('posisi', 'id'),
            ],
            'perusahaan_dilamar' => $perusahaanRules,
            'sumber_informasi_id' => [
                'required',
                'uuid',
                Rule::exists('sumber_informasi', 'id'),
            ],
            'nama_lengkap' => [
                'required',
                'string',
                'max:255',
            ],
            'nama_panggil' => [
                'nullable',
                'string',
                'max:255',
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'pendidikan_id' => [
                'nullable',
                'uuid',
                Rule::exists('pendidikan', 'id'),
            ],
            'jurusan' => [
                'nullable',
                'string',
                'max:255',
            ],
            'nama_institusi' => [
                'nullable',
                'string',
                'max:255',
            ],
            'agama_id' => [
                'nullable',
                'uuid',
                Rule::exists('agama', 'id'),
            ],
            'tanggal_lahir' => [
                'nullable',
                'date',
            ],
            'tanggal_skrining' => [
                'nullable',
                'date',
            ],
            'alamat_ktp' => [
                'nullable',
                'string',
            ],
            'alamat_domisili' => [
                'nullable',
                'string',
            ],
            'kewarganegaraan_id' => [
                'nullable',
                'uuid',
                Rule::exists('kewarganegaraan', 'id'),
            ],
            'status_pernikahan_id' => [
                'nullable',
                'uuid',
                Rule::exists('status_pernikahan', 'id'),
            ],
            'no_wa' => [
                'nullable',
                'string',
                'max:50',
            ],
            'gol_darah' => [
                'nullable',
                'string',
                'max:10',
            ],
            'tinggi_badan' => [
                'nullable',
                'numeric',
            ],
            'berat_badan' => [
                'nullable',
                'numeric',
            ],
            'str_aktif' => [
                'nullable',
                Rule::in(['active', 'non_active']),
            ],
        ], [
            'posisi_yang_dilamar.required' => 'Posisi yang dilamar wajib diisi.',
            'posisi_yang_dilamar.uuid' => 'Posisi yang dilamar tidak valid.',
            'posisi_yang_dilamar.exists' => 'Posisi yang dilamar tidak ditemukan.',

            'perusahaan_dilamar.required' => 'Perusahaan dilamar wajib diisi.',
            'perusahaan_dilamar.uuid' => 'Perusahaan dilamar tidak valid.',
            'perusahaan_dilamar.exists' => 'Perusahaan dilamar tidak ditemukan.',
            'perusahaan_dilamar.in' => 'Perusahaan dilamar tidak sesuai dengan perusahaan akun login.',

            'sumber_informasi_id.required' => 'Sumber informasi wajib diisi.',
            'sumber_informasi_id.uuid' => 'Sumber informasi tidak valid.',
            'sumber_informasi_id.exists' => 'Sumber informasi tidak ditemukan.',

            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);
    }


    private function makePendaftaranUrl(?string $token): ?string
    {
        if (!$token) {
            return null;
        }

        if (!empty($this->publicBaseUrl)) {
            return rtrim($this->publicBaseUrl, '/') . '/pendaftaran/' . $token;
        }

        return route('pendaftaran.show', [
            'token' => $token,
        ]);
    }

    private function appendExtraData(DataRiwayatDiri $data): DataRiwayatDiri
    {
        $data->pendaftaran_url = $this->makePendaftaranUrl($data->token);

        $data->id = $this->pelamarKey($data);

        $data->posisi_label = $this->relationValue($data->posisi, [
            'nama_posisi',
            'posisi',
            'nama',
            'jabatan',
            'nama_jabatan',
        ]);

        $data->perusahaan_label = $this->relationValue($data->perusahaan, [
            'nama_perusahaan',
            'perusahaan',
            'nama',
        ]);

        $data->created_by_label = $this->relationValue($data->creator, [
            'name',
            'nama',
            'email',
        ]);

        $data->dibuat_oleh_label = $data->created_by_label;

        $data->pendidikan_label = $this->relationValue($data->pendidikan, [
            'pendidikan',
            'nama',
        ]);

        $data->agama_label = $this->relationValue($data->agama, [
            'agama',
            'nama',
        ]);

        $data->kewarganegaraan_label = $this->relationValue($data->kewarganegaraan, [
            'kewarganegaraan',
            'nama',
        ]);

        $data->status_pernikahan_label = $this->relationValue($data->statusPernikahan, [
            'status_pernikahan',
            'status',
            'nama',
        ]);

        $data->sumber_informasi_label = $this->relationValue($data->sumberInformasi, [
            'informasi',
            'nama',
        ]);

        $completion = $this->calculateStepCompletion($data);

        $data->kelengkapan_form = $completion;
        $data->persentase_kelengkapan = $completion['percentage'];

        $data->total_step_terisi = $completion['completed_steps'];
        $data->total_step_form = $completion['total_steps'];
        $data->tahap_terakhir_form = $completion['last_completed_label'];

        $data->total_field_terisi = $completion['completed_steps'];
        $data->total_field_form = $completion['total_steps'];

        $dokumenInterview = $this->getDokumenInterview($data);

        $data->dokumen_interview = $dokumenInterview;
        $data->dokumenInterview = $dokumenInterview;
        $data->file_cv_interview = $dokumenInterview['file_cv'] ?? null;
        $data->fileCvInterview = $dokumenInterview['fileCv'] ?? null;
        $data->file_foto_interview = $dokumenInterview['file_foto'] ?? null;
        $data->fileFotoInterview = $dokumenInterview['fileFoto'] ?? null;

        return $data;
    }

    private function calculateStepCompletion(DataRiwayatDiri $data): array
    {
        $steps = [];
        $highestCompletedOrder = 0;
        $lastCompletedLabel = '-';

        foreach ($this->completionSteps as $step) {
            $isCompleted = $this->isCompletionStepCompleted($data, $step);

            if ($isCompleted && (int) $step['order'] > $highestCompletedOrder) {
                $highestCompletedOrder = (int) $step['order'];
                $lastCompletedLabel = $step['label'];
            }

            $steps[] = [
                'key' => $step['key'],
                'label' => $step['label'],
                'description' => $step['description'],
                'order' => $step['order'],
                'percentage' => $step['percentage'],
                'completed' => $isCompleted,
            ];
        }

        $totalSteps = count($this->completionSteps);
        $percentage = $totalSteps > 0
            ? round(($highestCompletedOrder / $totalSteps) * 100)
            : 0;

        $steps = collect($steps)
            ->map(function ($step) use ($highestCompletedOrder) {
                $step['completed'] = (int) $step['order'] <= $highestCompletedOrder;

                return $step;
            })
            ->values()
            ->all();

        return [
            'percentage' => $percentage,
            'completed_steps' => $highestCompletedOrder,
            'total_steps' => $totalSteps,
            'last_completed_label' => $lastCompletedLabel,
            'steps' => $steps,
        ];
    }

    private function isCompletionStepCompleted(DataRiwayatDiri $data, array $step): bool
    {
        foreach ($step['targets'] as $targetConfig) {
            $relation = $targetConfig['relation'] ?? null;
            $fields = $targetConfig['fields'] ?? [];

            $target = $relation
                ? ($data->{$relation} ?? null)
                : $data;

            if ($this->targetHasAnyFilledField($target, $fields)) {
                return true;
            }
        }

        return false;
    }

    private function targetHasAnyFilledField($target, array $fields): bool
    {
        if (!$target) {
            return false;
        }

        if ($target instanceof EloquentCollection || $target instanceof Collection) {
            if ($target->isEmpty()) {
                return false;
            }

            return $target->contains(function ($row) use ($fields) {
                return $this->modelHasAnyFilledField($row, $fields);
            });
        }

        return $this->modelHasAnyFilledField($target, $fields);
    }

    private function modelHasAnyFilledField($model, array $fields): bool
    {
        foreach ($fields as $field) {
            $value = null;

            if ($model instanceof Model) {
                $value = $model->getAttribute($field);
            } elseif (is_array($model)) {
                $value = $model[$field] ?? null;
            } elseif (is_object($model)) {
                $value = $model->{$field} ?? null;
            }

            if ($this->isFilledValue($value)) {
                return true;
            }
        }

        return false;
    }

    private function isFilledValue($value): bool
    {
        if ($value === null) {
            return false;
        }

        if (is_string($value)) {
            $trimmed = trim($value);

            if ($trimmed === '') {
                return false;
            }

            $lower = strtolower($trimmed);

            if (in_array($lower, ['[]', '{}', 'null'], true)) {
                return false;
            }

            $decoded = json_decode($trimmed, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $this->isFilledValue($decoded);
            }

            return true;
        }

        if (is_array($value)) {
            foreach ($value as $item) {
                if ($this->isFilledValue($item)) {
                    return true;
                }
            }

            return false;
        }

        if ($value instanceof Collection || $value instanceof EloquentCollection) {
            if ($value->isEmpty()) {
                return false;
            }

            return $value->contains(function ($item) {
                return $this->isFilledValue($item);
            });
        }

        if ($value instanceof Model) {
            return $this->modelHasAnyFilledField(
                $value,
                array_keys($value->getAttributes())
            );
        }

        if (is_object($value)) {
            return $this->isFilledValue((array) $value);
        }

        if (is_bool($value)) {
            return true;
        }

        if (is_numeric($value)) {
            return true;
        }

        return !empty($value);
    }

    private function relationValue($relation, array $columns): ?string
    {
        if (!$relation) {
            return null;
        }

        foreach ($columns as $column) {
            if (!empty($relation->{$column})) {
                return (string) $relation->{$column};
            }
        }

        return null;
    }

    private function openWaBaseUrl(): string
    {
        $url = rtrim(config('services.waha.url', env('WAHA_URL', 'https://wa.blast.dsicorp.id/api')), '/');

        // WAHA_URL boleh diisi https://domain atau https://domain/api.
        // Helper ini memastikan base URL final hanya punya satu /api.
        if (!Str::endsWith($url, '/api')) {
            $url .= '/api';
        }

        return $url;
    }

   private function sendWahaText(string $target, string $message): array
{
    $baseUrl = $this->openWaBaseUrl();
    $sessionId = config('services.waha.session', env('WAHA_SESSION', 'rekruitment'));

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

        Log::info('OpenWA send text response', [
            'url' => $url,
            'payload' => $payload,
            'http_code' => $response->status(),
            'response_json' => $json,
            'response_body' => $body,
        ]);

        if (!$response->successful()) {
            return [
                'success' => false,
                'chat_id' => $chatId,
                'response' => $json ?: $body,
                'message' => 'Gagal mengirim pesan melalui OpenWA. HTTP Code: '
                    . $response->status()
                    . '. Response: '
                    . ($body ?: json_encode($json)),
            ];
        }

        return [
            'success' => true,
            'chat_id' => $chatId,
            'response' => $json ?: $body,
            'message' => 'Pesan berhasil dikirim melalui OpenWA.',
        ];
    } catch (\Throwable $e) {
        Log::error('OpenWA send text exception', [
            'url' => $url,
            'target' => $target,
            'chat_id' => $chatId,
            'message' => $e->getMessage(),
        ]);

        return [
            'success' => false,
            'chat_id' => $chatId,
            'response' => null,
            'message' => 'Gagal mengirim pesan melalui OpenWA: ' . $e->getMessage(),
        ];
    }
}

    private function checkWahaSessionForSending(): array
    {
        $baseUrl = $this->openWaBaseUrl();
        $session = config('services.waha.session', env('WAHA_SESSION', 'rekruitment'));

        try {
            $response = Http::withoutVerifying()
                ->withHeaders($this->wahaHeaders())
                ->timeout(30)
                ->get($baseUrl . '/sessions');

            $json = $response->json();

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'session' => $session,
                    'status' => 'error',
                    'message' => 'Gagal mengecek session WAHA. URL: ' . $baseUrl . '/sessions. HTTP Code: ' . $response->status(),
                    'device_number' => null,
                    'device_status' => null,
                    'waha_response' => $json ?: $response->body(),
                ];
            }

            $sessionData = $this->extractWahaSessionData($json, $session);

            if (empty($sessionData)) {
                return [
                    'success' => false,
                    'session' => $session,
                    'status' => 'not_found',
                    'message' => 'Session WAHA "' . $session . '" tidak ditemukan. Pastikan WAHA_SESSION sama dengan nama session di OpenWA.',
                    'device_number' => null,
                    'device_status' => null,
                    'waha_response' => $json,
                ];
            }

            $deviceStatus = strtolower((string) ($sessionData['status'] ?? $sessionData['device_status'] ?? ''));
            $deviceNumber = $this->extractWahaPhoneNumber($sessionData);

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
                    'session' => $session,
                    'status' => 'disconnected',
                    'message' => 'Session WAHA belum connect. Status saat ini: ' . ($deviceStatus ?: '-'),
                    'device_number' => $deviceNumber,
                    'device_status' => $deviceStatus ?: null,
                    'waha_response' => $json,
                ];
            }

            return [
                'success' => true,
                'session' => $session,
                'status' => 'connected',
                'message' => 'Session WAHA sudah connect.',
                'device_number' => $deviceNumber,
                'device_status' => $deviceStatus,
                'waha_response' => $json,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'session' => $session,
                'status' => 'error',
                'message' => 'Gagal memvalidasi WAHA: ' . $e->getMessage(),
                'device_number' => null,
                'device_status' => null,
            ];
        }
    }


    private function wahaHeaders(): array
    {
        $apiKey = config('services.waha.api_key', env('WAHA_API_KEY'));

        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        if (!empty($apiKey)) {
            $headers['X-Api-Key'] = $apiKey;
        }

        return $headers;
    }

    private function extractWahaSessionData($json, string $session): array
    {
        if (is_array($json) && array_is_list($json)) {
            foreach ($json as $item) {
                if (!is_array($item)) {
                    continue;
                }

                if (($item['name'] ?? null) === $session || ($item['session'] ?? null) === $session) {
                    return $item;
                }
            }

            return is_array($json[0] ?? null) ? $json[0] : [];
        }

        return is_array($json) ? $json : [];
    }

    private function extractWahaPhoneNumber(array $sessionData): ?string
    {
        $candidates = [
            $sessionData['phone'] ?? null,
            $sessionData['phoneNumber'] ?? null,
            $sessionData['phone_number'] ?? null,
            $sessionData['me']['id'] ?? null,
            $sessionData['me']['user'] ?? null,
            $sessionData['me']['number'] ?? null,
            $sessionData['me']['phone'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            $number = $this->normalizeWhatsappNumber($candidate);

            if ($number) {
                return $number;
            }
        }

        return null;
    }

    private function buildPesanSkrining(DataRiwayatDiri $pelamar, ?string $template = null): string
    {
        $tanggalSkrining = $pelamar->tanggal_skrining
            ? date('d F Y', strtotime($pelamar->tanggal_skrining))
            : '-';

        $nama = $pelamar->nama_panggil ?: ($pelamar->nama_lengkap ?: 'Kandidat');
        $namaLengkap = $pelamar->nama_lengkap ?: $nama;
        $posisi = $pelamar->posisi_label ?: '-';
        $perusahaan = $pelamar->perusahaan_label ?: '-';
        $nomerPerusahaan = $this->normalizeWhatsappNumber($pelamar->perusahaan?->no_wa ?? null);
        $nomerPerusahaanLabel = $nomerPerusahaan ?: ($pelamar->perusahaan?->no_wa ?? '-');
        $url = $pelamar->pendaftaran_url ?: $this->makePendaftaranUrl($pelamar->token ?? null);

        $message = $template ?: "Halo {nama},\n\n"
            . "Terima kasih sudah mengikuti proses skrining kandidat untuk posisi {posisi} di {perusahaan}.\n"
            . "Mohon untuk selalu mengecek dan melengkapi data diri Anda melalui link pendaftaran berikut.\n"
            . "Klik/buka link ini:\n"
            . "{url_pendaftaran}\n\n"
            . "Pastikan data diri, riwayat keluarga, riwayat kesehatan, riwayat pekerjaan, dan kesiapan bekerja sudah diisi dengan benar dan lengkap.\n\n"
            . "Jika ada kendala, silakan hubungi WA ini.\n\n"
            . "Terima kasih.\n"
            . "Tim Rekrutmen {perusahaan}";

        return strtr($message, [
            '{nama}' => $nama,
            '{nama_lengkap}' => $namaLengkap,
            '{posisi}' => $posisi,
            '{perusahaan}' => $perusahaan,
            '{tanggal_skrining}' => $tanggalSkrining,
            '{token}' => (string) ($pelamar->token ?? ''),
            '{url_pendaftaran}' => (string) $url,
            '{link_pendaftaran}' => (string) $url,
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


    private function getDokumenInterview(DataRiwayatDiri $data): array
    {
        $empty = [
            'jadwal_interview_kandidat_id' => null,
            'jadwalInterviewKandidatId' => null,
            'jadwal_interview_id' => null,
            'jadwalInterviewId' => null,
            'judul_interview' => null,
            'judulInterview' => null,
            'jadwal_interview' => null,
            'jadwalInterview' => null,
            'tanggal_interview' => null,
            'tanggalInterview' => null,
            'file_cv' => null,
            'fileCv' => null,
            'file_cv_url' => null,
            'fileCvUrl' => null,
            'file_foto' => null,
            'fileFoto' => null,
            'file_foto_url' => null,
            'fileFotoUrl' => null,
            'ada_dokumen' => false,
            'adaDokumen' => false,
        ];

        if (!Schema::hasTable('jadwal_interview_kandidat')) {
            return $empty;
        }

        $query = DB::table('jadwal_interview_kandidat as jik')
            ->where('jik.data_riwayat_diri_id', $this->pelamarKey($data));

        if (Schema::hasColumn('jadwal_interview_kandidat', 'deleted_at')) {
            $query->whereNull('jik.deleted_at');
        }

        $select = [
            'jik.id as jadwal_interview_kandidat_id',
            'jik.jadwal_interview_id',
            DB::raw(Schema::hasColumn('jadwal_interview_kandidat', 'file_cv') ? 'jik.file_cv as file_cv' : 'NULL as file_cv'),
            DB::raw(Schema::hasColumn('jadwal_interview_kandidat', 'file_foto') ? 'jik.file_foto as file_foto' : 'NULL as file_foto'),
            DB::raw(Schema::hasColumn('jadwal_interview_kandidat', 'created_at') ? 'jik.created_at as created_at' : 'NULL as created_at'),
            DB::raw(Schema::hasColumn('jadwal_interview_kandidat', 'updated_at') ? 'jik.updated_at as updated_at' : 'NULL as updated_at'),
        ];

        if (Schema::hasTable('jadwal_interview')) {
            $query->leftJoin('jadwal_interview as ji', 'ji.id', '=', 'jik.jadwal_interview_id');

            if (Schema::hasColumn('jadwal_interview', 'deleted_at')) {
                $query->whereNull('ji.deleted_at');
            }

            $select[] = DB::raw(Schema::hasColumn('jadwal_interview', 'judul_interview') ? 'ji.judul_interview as judul_interview' : 'NULL as judul_interview');
            $select[] = DB::raw(Schema::hasColumn('jadwal_interview', 'jadwal_interview') ? 'ji.jadwal_interview as jadwal_interview' : 'NULL as jadwal_interview');
        } else {
            $select[] = DB::raw('NULL as judul_interview');
            $select[] = DB::raw('NULL as jadwal_interview');
        }

        $query->select($select);

        if (Schema::hasColumn('jadwal_interview_kandidat', 'file_cv') || Schema::hasColumn('jadwal_interview_kandidat', 'file_foto')) {
            $fileCvColumn = Schema::hasColumn('jadwal_interview_kandidat', 'file_cv') ? 'jik.file_cv' : 'NULL';
            $fileFotoColumn = Schema::hasColumn('jadwal_interview_kandidat', 'file_foto') ? 'jik.file_foto' : 'NULL';

            $query->orderByRaw("(CASE WHEN {$fileCvColumn} IS NOT NULL OR {$fileFotoColumn} IS NOT NULL THEN 1 ELSE 0 END) DESC");
        }

        if (Schema::hasTable('jadwal_interview') && Schema::hasColumn('jadwal_interview', 'jadwal_interview')) {
            $query->orderByDesc('ji.jadwal_interview');
        } elseif (Schema::hasColumn('jadwal_interview_kandidat', 'created_at')) {
            $query->orderByDesc('jik.created_at');
        }

        if (Schema::hasColumn('jadwal_interview_kandidat', 'updated_at')) {
            $query->orderByDesc('jik.updated_at');
        }

        $row = $query->first();

        if (!$row) {
            return $empty;
        }

        $fileCv = $this->normalizeFileUrl($row->file_cv ?? null);
        $fileFoto = $this->normalizeFileUrl($row->file_foto ?? null);
        $hasDocument = !empty($fileCv) || !empty($fileFoto);

        return [
            'jadwal_interview_kandidat_id' => $row->jadwal_interview_kandidat_id ?? null,
            'jadwalInterviewKandidatId' => $row->jadwal_interview_kandidat_id ?? null,
            'jadwal_interview_id' => $row->jadwal_interview_id ?? null,
            'jadwalInterviewId' => $row->jadwal_interview_id ?? null,
            'judul_interview' => $row->judul_interview ?? null,
            'judulInterview' => $row->judul_interview ?? null,
            'jadwal_interview' => $row->jadwal_interview ?? null,
            'jadwalInterview' => $row->jadwal_interview ?? null,
            'tanggal_interview' => $row->jadwal_interview ?? null,
            'tanggalInterview' => $row->jadwal_interview ?? null,
            'file_cv' => $fileCv,
            'fileCv' => $fileCv,
            'file_cv_url' => $fileCv,
            'fileCvUrl' => $fileCv,
            'file_foto' => $fileFoto,
            'fileFoto' => $fileFoto,
            'file_foto_url' => $fileFoto,
            'fileFotoUrl' => $fileFoto,
            'ada_dokumen' => $hasDocument,
            'adaDokumen' => $hasDocument,
            'created_at' => $row->created_at ?? null,
            'updated_at' => $row->updated_at ?? null,
        ];
    }

    private function normalizeFileUrl(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if (Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }

        if (Str::startsWith($value, '/storage/')) {
            return url($value);
        }

        if (Str::startsWith($value, 'storage/')) {
            return url('/' . $value);
        }

        return url(Storage::url(ltrim($value, '/')));
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



    private function pelamarKey(DataRiwayatDiri $pelamar): ?string
    {
        return $pelamar->getKey() ? (string) $pelamar->getKey() : null;
    }

    private function scopedPelamarQuery()
    {
        $query = DataRiwayatDiri::query();

        $allowedPerusahaanIds = $this->currentUserPerusahaanIds();

        if (is_array($allowedPerusahaanIds)) {
            $query->whereIn('perusahaan_dilamar', $allowedPerusahaanIds);
        }

        return $query;
    }

    private function findScopedPelamarOrFail(string $id, bool $withRelations = false): DataRiwayatDiri
    {
        $query = $this->scopedPelamarQuery();

        if ($withRelations) {
            $query->with($this->safeRelations());
        }

        return $query->where('id', $id)->firstOrFail();
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
         * Fallback jika sistem lama masih punya users.perusahaan_id.
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

    private function safeRelations(): array
    {
        return $this->relations;
    }
}