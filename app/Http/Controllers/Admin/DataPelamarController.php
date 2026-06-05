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
use Illuminate\Support\Facades\Http;
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
                        'posisi_yang_dilamar',
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
        $validated = $request->validate([
            'tanggal_skrining_mulai' => ['nullable', 'date'],
            'tanggal_skrining_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_skrining_mulai'],
        ]);

        $query = DataRiwayatDiri::query()
            ->with($this->safeRelations());

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
                'tanggal_skrining_mulai' => $validated['tanggal_skrining_mulai'] ?? null,
                'tanggal_skrining_selesai' => $validated['tanggal_skrining_selesai'] ?? null,
            ],
            'data' => $data,
        ]);
    }


    public function kirimPesanSkrining(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tanggal_skrining_mulai' => ['required', 'date'],
            'tanggal_skrining_selesai' => ['required', 'date', 'after_or_equal:tanggal_skrining_mulai'],
            'message_template' => ['nullable', 'string', 'max:5000'],
        ], [
            'tanggal_skrining_mulai.required' => 'Tanggal skrining mulai wajib diisi.',
            'tanggal_skrining_selesai.required' => 'Tanggal skrining selesai wajib diisi.',
            'tanggal_skrining_selesai.after_or_equal' => 'Tanggal skrining selesai tidak boleh lebih kecil dari tanggal mulai.',
        ]);

        $pelamars = DataRiwayatDiri::query()
            ->with($this->safeRelations())
            ->whereDate('tanggal_skrining', '>=', $validated['tanggal_skrining_mulai'])
            ->whereDate('tanggal_skrining', '<=', $validated['tanggal_skrining_selesai'])
            ->orderBy('tanggal_skrining', 'asc')
            ->orderBy('nama_lengkap', 'asc')
            ->get()
            ->map(function ($item) {
                return $this->appendExtraData($item);
            });

        if ($pelamars->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada pelamar pada tanggal skrining tersebut.',
                'total_pelamar' => 0,
                'total_dikirim' => 0,
                'total_dilewati' => 0,
                'skipped' => [],
            ], 404);
        }

        $groupedMessages = [];
        $skipped = [];

        foreach ($pelamars as $pelamar) {
            $target = $this->normalizeWhatsappNumber($pelamar->no_wa ?? null);
            $urlPendaftaran = $pelamar->pendaftaran_url ?: $this->makePendaftaranUrl($pelamar->token ?? null);
            $perusahaan = $pelamar->perusahaan;

            if (!$target) {
                $skipped[] = [
                    'id' => $pelamar->id,
                    'nama_lengkap' => $pelamar->nama_lengkap,
                    'no_wa' => $pelamar->no_wa,
                    'perusahaan' => $pelamar->perusahaan_label,
                    'pendaftaran_url' => $urlPendaftaran,
                    'reason' => 'Nomor WhatsApp pelamar kosong atau tidak valid.',
                ];

                continue;
            }

            if (!$urlPendaftaran) {
                $skipped[] = [
                    'id' => $pelamar->id,
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
                    'id' => $pelamar->id,
                    'nama_lengkap' => $pelamar->nama_lengkap,
                    'no_wa' => $pelamar->no_wa,
                    'target' => $target,
                    'perusahaan' => $pelamar->perusahaan_label,
                    'pendaftaran_url' => $urlPendaftaran,
                    'reason' => 'Data perusahaan kandidat tidak ditemukan.',
                ];

                continue;
            }

            $tokenApiWa = $this->normalizeTokenApiWa($perusahaan->token_api_wa ?? null);
            $nomerPerusahaan = $this->normalizeWhatsappNumber($perusahaan->no_wa ?? null);

            if (!$nomerPerusahaan) {
                $skipped[] = [
                    'id' => $pelamar->id,
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

            if (!$tokenApiWa) {
                $skipped[] = [
                    'id' => $pelamar->id,
                    'nama_lengkap' => $pelamar->nama_lengkap,
                    'no_wa' => $pelamar->no_wa,
                    'target' => $target,
                    'perusahaan_id' => $perusahaan->id ?? null,
                    'perusahaan' => $perusahaan->nama_perusahaan ?? $pelamar->perusahaan_label,
                    'nomer_perusahaan' => $nomerPerusahaan,
                    'pendaftaran_url' => $urlPendaftaran,
                    'reason' => 'Token API WA perusahaan kosong.',
                ];

                continue;
            }

            $groupKey = (string) ($perusahaan->id ?? $tokenApiWa);

            if (!isset($groupedMessages[$groupKey])) {
                $groupedMessages[$groupKey] = [
                    'perusahaan_id' => $perusahaan->id ?? null,
                    'perusahaan' => $perusahaan->nama_perusahaan ?? $pelamar->perusahaan_label,
                    'nomer_perusahaan' => $nomerPerusahaan,
                    'token_api_wa' => $tokenApiWa,
                    'messages' => [],
                ];
            }

            $groupedMessages[$groupKey]['messages'][] = [
                'target' => $target,
                'message' => $this->buildPesanSkrining(
                    $pelamar,
                    $validated['message_template'] ?? null
                ),
                'delay' => '2',
            ];
        }

        if (empty($groupedMessages)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data valid untuk dikirim pesan. Pastikan nomor WhatsApp pelamar, nomor WhatsApp perusahaan, token API WA perusahaan, dan token pendaftaran kandidat sudah tersedia.',
                'tanggal_skrining_mulai' => $validated['tanggal_skrining_mulai'],
                'tanggal_skrining_selesai' => $validated['tanggal_skrining_selesai'],
                'total_pelamar' => $pelamars->count(),
                'total_dikirim' => 0,
                'total_dilewati' => count($skipped),
                'skipped' => $skipped,
            ], 422);
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

                $targets = array_merge(
                    $targets,
                    collect($group['messages'])->pluck('target')->values()->all()
                );

                $responses[] = [
                    'success' => $isSuccess,
                    'perusahaan_id' => $group['perusahaan_id'],
                    'perusahaan' => $group['perusahaan'],
                    'nomer_perusahaan' => $group['nomer_perusahaan'],
                    'total_data' => $countMessages,
                    'targets' => collect($group['messages'])->pluck('target')->values(),
                    'fonnte_http_code' => $response->status(),
                    'fonnte_response' => $json ?: $response->body(),
                    'message' => $isSuccess
                        ? 'Pesan berhasil dikirim ke antrean Fonnte untuk perusahaan ini.'
                        : ($json['reason'] ?? $json['detail'] ?? $json['message'] ?? 'Pesan gagal dikirim melalui Fonnte untuk perusahaan ini.'),
                ];
            } catch (\Throwable $e) {
                $countMessages = count($group['messages']);
                $totalGagalProvider += $countMessages;

                Log::error('Gagal mengirim pesan Fonnte skrining per perusahaan', [
                    'message' => $e->getMessage(),
                    'perusahaan_id' => $group['perusahaan_id'],
                    'perusahaan' => $group['perusahaan'],
                    'tanggal_skrining_mulai' => $validated['tanggal_skrining_mulai'],
                    'tanggal_skrining_selesai' => $validated['tanggal_skrining_selesai'],
                ]);

                $responses[] = [
                    'success' => false,
                    'perusahaan_id' => $group['perusahaan_id'],
                    'perusahaan' => $group['perusahaan'],
                    'nomer_perusahaan' => $group['nomer_perusahaan'],
                    'total_data' => $countMessages,
                    'targets' => collect($group['messages'])->pluck('target')->values(),
                    'message' => 'Terjadi kesalahan saat mengirim pesan Fonnte untuk perusahaan ini: ' . $e->getMessage(),
                ];
            }
        }

        $isAllSuccess = $totalDikirim > 0 && $totalGagalProvider === 0;
        $isPartialSuccess = $totalDikirim > 0 && $totalGagalProvider > 0;

        return response()->json([
            'success' => $totalDikirim > 0,
            'message' => $isAllSuccess
                ? 'Pesan WhatsApp berhasil dikirim sesuai perusahaan masing-masing.'
                : ($isPartialSuccess
                    ? 'Sebagian pesan WhatsApp berhasil dikirim, sebagian gagal. Cek detail response per perusahaan.'
                    : 'Pesan WhatsApp gagal dikirim. Cek detail response per perusahaan.'),
            'tanggal_skrining_mulai' => $validated['tanggal_skrining_mulai'],
            'tanggal_skrining_selesai' => $validated['tanggal_skrining_selesai'],
            'total_pelamar' => $pelamars->count(),
            'total_data' => $pelamars->count(),
            'total_dikirim' => $totalDikirim,
            'total_dilewati' => count($skipped),
            'total_gagal_provider' => $totalGagalProvider,
            'total_perusahaan' => count($groupedMessages),
            'skipped' => $skipped,
            'targets' => array_values(array_unique($targets)),
            'perusahaan_responses' => $responses,
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
        $data = DataPerusahaan::query()
            ->orderBy('nama_perusahaan')
            ->get([
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
        $data = DataRiwayatDiri::query()
            ->with($this->safeRelations())
            ->findOrFail($id);

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
        $data = DataRiwayatDiri::query()
            ->with($this->safeRelations())
            ->findOrFail($id);

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
        $data = DataRiwayatDiri::query()->findOrFail($id);

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
        $data = DataRiwayatDiri::query()->findOrFail($id);

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
        return $request->validate([
            'posisi_yang_dilamar' => [
                'required',
                'uuid',
                Rule::exists('posisi', 'id'),
            ],
            'perusahaan_dilamar' => [
                'required',
                'uuid',
                Rule::exists('data_perusahaan', 'id'),
            ],
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
            . "Tanggal skrining Anda: {tanggal_skrining}.\n\n"
            . "Mohon untuk selalu mengecek dan melengkapi data diri Anda melalui link pendaftaran berikut.\n"
            . "Klik/buka link ini:\n"
            . "{url_pendaftaran}\n\n"
            . "Pastikan data diri, riwayat keluarga, riwayat kesehatan, riwayat pekerjaan, dan kesiapan bekerja sudah diisi dengan benar dan lengkap.\n\n"
            . "Jika ada kendala, silakan hubungi nomor perusahaan: {nomer_perusahaan}.\n\n"
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

    private function safeRelations(): array
    {
        return $this->relations;
    }
}