<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataRiwayatDiri;
use App\Models\HasilReviewManagement;
use App\Models\JadwalInterviewKandidat;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class ReviewManagementController extends Controller
{
    private array $statusReviewOptions = [
        'Diterima',
        'Gagal',
    ];

    private array $hasilInterviewReviewOptions = [
        'Lolos Interview',
        'Dipertimbangkan',
    ];

    private array $hasilTestReviewOptions = [
        'lolos',
    ];

    private array $reviewSourceOptions = [
        'interview',
        'test_zoom',
        'test_mmpi',
    ];

    private array $pelamarRelations = [
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
        return view('pages.admin.index');
    }

    public function list(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
        ]);

        $tanggalMulai = $validated['tanggal_mulai'] ?? now()->toDateString();
        $tanggalSelesai = $validated['tanggal_selesai'] ?? now()->toDateString();

        $data = collect()
            ->merge($this->listReviewInterview($tanggalMulai, $tanggalSelesai))
            ->merge($this->listReviewHasilTestZoom($tanggalMulai, $tanggalSelesai))
            ->merge($this->listReviewHasilTestMmpi($tanggalMulai, $tanggalSelesai))
            ->sortByDesc('tanggal_sort')
            ->values()
            ->map(function (array $row) {
                unset($row['tanggal_sort']);

                return $row;
            });

        return response()->json([
            'success' => true,
            'message' => 'Data review management berhasil diambil.',
            'filter' => [
                'tanggal_mulai' => $tanggalMulai,
                'tanggal_selesai' => $tanggalSelesai,
            ],
            'data' => $data,
        ]);
    }

    private function listReviewInterview(string $tanggalMulai, string $tanggalSelesai): Collection
    {
        return HasilReviewManagement::query()
            ->with([
                'hasilInterview.jadwalInterview:id,judul_interview,jadwal_interview',
                'hasilInterview.kandidat' => function ($query) {
                    $query->with($this->safePelamarRelations());
                },
            ])
            ->whereHas('hasilInterview', function ($query) {
                $query->whereIn('hasil_interview', $this->hasilInterviewReviewOptions);
            })
            ->whereHas('hasilInterview.jadwalInterview', function ($query) use ($tanggalMulai, $tanggalSelesai) {
                $query
                    ->whereDate('jadwal_interview', '>=', $tanggalMulai)
                    ->whereDate('jadwal_interview', '<=', $tanggalSelesai);
            })
            ->latest()
            ->get()
            ->map(function (HasilReviewManagement $review) {
                $item = $review->hasilInterview;
                $kandidat = $item?->kandidat;
                $jadwalInterview = $item?->jadwalInterview;

                if ($kandidat) {
                    $kandidat = $this->appendExtraData($kandidat);
                }

                $latestTestZoom = $this->getLatestHasilTestZoomForKandidat($item?->data_riwayat_diri_id);
                $latestTestMmpi = $this->getLatestHasilTestMmpiForKandidat($item?->data_riwayat_diri_id);

                return [
                    'id' => $item?->id,
                    'source_id' => $item?->id,
                    'review_source' => 'interview',
                    'review_source_label' => 'Interview',
                    'jenis_review' => 'Interview',
                    'hasil_interview_id' => $item?->id,
                    'hasil_test_zoom_id' => null,
                    'hasil_test_mmpi_id' => null,
                    'jadwal_interview_id' => $item?->jadwal_interview_id,
                    'data_riwayat_diri_id' => $item?->data_riwayat_diri_id,

                    'judul_tahapan' => $jadwalInterview?->judul_interview,
                    'tanggal_tahapan' => $jadwalInterview?->jadwal_interview,
                    'tanggal_tahapan_format' => $this->formatTanggalWaktuIndonesia($jadwalInterview?->jadwal_interview),

                    'judul_interview' => $jadwalInterview?->judul_interview,
                    'tanggal_interview' => $jadwalInterview?->jadwal_interview,
                    'tanggal_interview_format' => $this->formatTanggalWaktuIndonesia($jadwalInterview?->jadwal_interview),

                    'nama_kandidat' => $kandidat?->nama_lengkap ?? '-',
                    'email_kandidat' => $kandidat?->email,
                    'no_wa_kandidat' => $kandidat?->no_wa,
                    'posisi_label' => $kandidat?->posisi_label,
                    'perusahaan_label' => $kandidat?->perusahaan_label,

                    'status_kehadiran' => $item?->status_kehadiran,
                    'hasil_interview' => $item?->hasil_interview,
                    'hasil_test' => null,
                    'hasil_label' => $item?->hasil_interview,
                    'latest_test_zoom' => $latestTestZoom,
                    'latest_test_mmpi' => $latestTestMmpi,
                    'catatan' => $item?->catatan,
                    'created_at' => $review->created_at,
                    'updated_at' => $review->updated_at,

                    'review_management_id' => $review->id,
                    'review_management' => $review->review_management,
                    'status_review' => $review->status,

                    'detail_kandidat' => $kandidat,
                    'tanggal_sort' => $jadwalInterview?->jadwal_interview ? strtotime((string) $jadwalInterview->jadwal_interview) : 0,
                ];
            });
    }

    private function listReviewHasilTestZoom(string $tanggalMulai, string $tanggalSelesai): Collection
    {
        if (!Schema::hasTable('daftar_hadir_test_zoom') ||
            !Schema::hasTable('jadwal_test_zoom') ||
            !Schema::hasTable('hasil_review_management') ||
            !Schema::hasColumn('hasil_review_management', 'daftar_hadir_test_zoom_id')) {
            return collect();
        }

        $query = DB::table('daftar_hadir_test_zoom as dhz')
            ->join('jadwal_test_zoom as jtz', 'jtz.id', '=', 'dhz.jadwal_test_zoom_id')
            ->join('data_riwayat_diri as drd', 'drd.id', '=', 'dhz.data_riwayat_diri_id')
            ->leftJoin('hasil_review_management as hrm', 'hrm.daftar_hadir_test_zoom_id', '=', 'dhz.id')
            ->leftJoin('posisi as p', 'p.id', '=', 'drd.posisi_yang_dilamar')
            ->leftJoin('data_perusahaan as dp', 'dp.id', '=', 'drd.perusahaan_dilamar')
            ->whereDate('jtz.jadwal', '>=', $tanggalMulai)
            ->whereDate('jtz.jadwal', '<=', $tanggalSelesai)
            ->whereRaw("LOWER(REPLACE(REPLACE(TRIM(COALESCE(dhz.hasil_test, '')), ' ', '_'), '-', '_')) IN ('lolos', '1', 'true', 'ya', 'yes')");

        if (Schema::hasColumn('daftar_hadir_test_zoom', 'deleted_at')) {
            $query->whereNull('dhz.deleted_at');
        }

        if (Schema::hasColumn('jadwal_test_zoom', 'deleted_at')) {
            $query->whereNull('jtz.deleted_at');
        }

        if (Schema::hasColumn('data_riwayat_diri', 'deleted_at')) {
            $query->whereNull('drd.deleted_at');
        }

        $selects = [
            'dhz.id as hasil_test_zoom_id',
            'dhz.jadwal_test_zoom_id',
            'dhz.data_riwayat_diri_id',
            'dhz.tanggal_kehadiran',
            'dhz.status_kehadiran',
            'dhz.hasil_test',
            Schema::hasColumn('daftar_hadir_test_zoom', 'file_hasil_test_zoom')
                ? 'dhz.file_hasil_test_zoom'
                : DB::raw('NULL as file_hasil_test_zoom'),
            'dhz.created_at as hasil_test_created_at',
            'dhz.updated_at as hasil_test_updated_at',
            'jtz.jadwal as tanggal_test',
            'drd.nama_lengkap',
            'drd.nama_panggil',
            'drd.email',
            'drd.no_wa',
            'p.nama_posisi',
            'dp.nama_perusahaan',
            'hrm.id as review_management_id',
            'hrm.review_management',
            'hrm.status as status_review',
            'hrm.created_at as review_created_at',
            'hrm.updated_at as review_updated_at',
        ];

        foreach (['hasil_test_iq', 'hasil_test_disc', 'hasil_test_eysenck'] as $column) {
            $selects[] = Schema::hasColumn('daftar_hadir_test_zoom', $column)
                ? "dhz.{$column}"
                : DB::raw("NULL as {$column}");
        }

        return $query
            ->select($selects)
            ->orderByDesc('jtz.jadwal')
            ->get()
            ->map(function ($row) {
                $kandidat = DataRiwayatDiri::query()
                    ->with($this->safePelamarRelations())
                    ->find($row->data_riwayat_diri_id);

                if ($kandidat) {
                    $kandidat = $this->appendExtraData($kandidat);
                }

                $nama = $kandidat?->nama_lengkap
                    ?: ($row->nama_lengkap ?: ($row->nama_panggil ?: '-'));

                $catatanTest = collect([
                    'IQ' => $row->hasil_test_iq ?? null,
                    'DISC' => $row->hasil_test_disc ?? null,
                    'Eysenck' => $row->hasil_test_eysenck ?? null,
                ])
                    ->filter(fn ($value) => filled($value))
                    ->map(fn ($value, $key) => "{$key}: {$value}")
                    ->implode(' | ');

                return [
                    'id' => $row->hasil_test_zoom_id,
                    'source_id' => $row->hasil_test_zoom_id,
                    'review_source' => 'test_zoom',
                    'review_source_label' => 'Hasil Test Zoom',
                    'jenis_review' => 'Hasil Test Zoom',
                    'hasil_interview_id' => null,
                    'hasil_test_zoom_id' => $row->hasil_test_zoom_id,
                    'hasil_test_mmpi_id' => null,
                    'jadwal_test_zoom_id' => $row->jadwal_test_zoom_id,
                    'data_riwayat_diri_id' => $row->data_riwayat_diri_id,

                    'judul_tahapan' => 'Hasil Test Zoom',
                    'tanggal_tahapan' => $row->tanggal_test,
                    'tanggal_tahapan_format' => $this->formatTanggalWaktuIndonesia($row->tanggal_test),

                    'judul_interview' => 'Hasil Test Zoom',
                    'tanggal_interview' => $row->tanggal_test,
                    'tanggal_interview_format' => $this->formatTanggalWaktuIndonesia($row->tanggal_test),

                    'nama_kandidat' => $nama,
                    'email_kandidat' => $kandidat?->email ?? $row->email,
                    'no_wa_kandidat' => $kandidat?->no_wa ?? $row->no_wa,
                    'posisi_label' => $kandidat?->posisi_label ?? $row->nama_posisi,
                    'perusahaan_label' => $kandidat?->perusahaan_label ?? $row->nama_perusahaan,

                    'status_kehadiran' => $this->labelKehadiran($row->status_kehadiran),
                    'hasil_interview' => null,
                    'hasil_test' => $this->normalizeHasilTest($row->hasil_test),
                    'hasil_label' => $this->labelHasilTest($row->hasil_test),
                    'file_hasil_test_zoom' => $row->file_hasil_test_zoom ?? null,
                    'file_hasil_test_zoom_url' => $this->makeFileUrl($row->file_hasil_test_zoom ?? null),
                    'hasil_test_iq' => $row->hasil_test_iq ?? null,
                    'hasil_test_disc' => $row->hasil_test_disc ?? null,
                    'hasil_test_eysenck' => $row->hasil_test_eysenck ?? null,
                    'catatan' => $catatanTest ?: null,
                    'created_at' => $row->review_created_at ?? $row->hasil_test_created_at,
                    'updated_at' => $row->review_updated_at ?? $row->hasil_test_updated_at,

                    'review_management_id' => $row->review_management_id,
                    'review_management' => $row->review_management,
                    'status_review' => $row->status_review,

                    'detail_kandidat' => $kandidat,
                    'tanggal_sort' => $row->tanggal_test ? strtotime((string) $row->tanggal_test) : 0,
                ];
            });
    }

    private function listReviewHasilTestMmpi(string $tanggalMulai, string $tanggalSelesai): Collection
    {
        if (!Schema::hasTable('daftar_hadir_test_mmpi') ||
            !Schema::hasTable('jadwal_test_mmpi') ||
            !Schema::hasTable('hasil_review_management') ||
            !Schema::hasColumn('hasil_review_management', 'daftar_hadir_test_mmpi_id')) {
            return collect();
        }

        $query = DB::table('daftar_hadir_test_mmpi as dhm')
            ->join('jadwal_test_mmpi as jtm', 'jtm.id', '=', 'dhm.jadwal_test_mmpi_id')
            ->join('data_riwayat_diri as drd', 'drd.id', '=', 'dhm.data_riwayat_diri_id')
            ->leftJoin('hasil_review_management as hrm', 'hrm.daftar_hadir_test_mmpi_id', '=', 'dhm.id')
            ->leftJoin('posisi as p', 'p.id', '=', 'drd.posisi_yang_dilamar')
            ->leftJoin('data_perusahaan as dp', 'dp.id', '=', 'drd.perusahaan_dilamar')
            ->whereDate('jtm.tanggal', '>=', $tanggalMulai)
            ->whereDate('jtm.tanggal', '<=', $tanggalSelesai)
            ->whereRaw("LOWER(REPLACE(REPLACE(TRIM(COALESCE(dhm.hasil_test, '')), ' ', '_'), '-', '_')) IN ('lolos', '1', 'true', 'ya', 'yes')");

        if (Schema::hasColumn('daftar_hadir_test_mmpi', 'deleted_at')) {
            $query->whereNull('dhm.deleted_at');
        }

        if (Schema::hasColumn('jadwal_test_mmpi', 'deleted_at')) {
            $query->whereNull('jtm.deleted_at');
        }

        if (Schema::hasColumn('data_riwayat_diri', 'deleted_at')) {
            $query->whereNull('drd.deleted_at');
        }

        return $query
            ->select([
                'dhm.id as hasil_test_mmpi_id',
                'dhm.jadwal_test_mmpi_id',
                'dhm.data_riwayat_diri_id',
                'dhm.tanggal_kehadiran',
                'dhm.status_kehadiran',
                'dhm.hasil_test',
                Schema::hasColumn('daftar_hadir_test_mmpi', 'file_hasil_test_mmpi')
                    ? 'dhm.file_hasil_test_mmpi'
                    : DB::raw('NULL as file_hasil_test_mmpi'),
                'dhm.created_at as hasil_test_created_at',
                'dhm.updated_at as hasil_test_updated_at',
                'jtm.tanggal as tanggal_test',
                'drd.nama_lengkap',
                'drd.nama_panggil',
                'drd.email',
                'drd.no_wa',
                'p.nama_posisi',
                'dp.nama_perusahaan',
                'hrm.id as review_management_id',
                'hrm.review_management',
                'hrm.status as status_review',
                'hrm.created_at as review_created_at',
                'hrm.updated_at as review_updated_at',
            ])
            ->orderByDesc('jtm.tanggal')
            ->get()
            ->map(function ($row) {
                $kandidat = DataRiwayatDiri::query()
                    ->with($this->safePelamarRelations())
                    ->find($row->data_riwayat_diri_id);

                if ($kandidat) {
                    $kandidat = $this->appendExtraData($kandidat);
                }

                $nama = $kandidat?->nama_lengkap
                    ?: ($row->nama_lengkap ?: ($row->nama_panggil ?: '-'));

                return [
                    'id' => $row->hasil_test_mmpi_id,
                    'source_id' => $row->hasil_test_mmpi_id,
                    'review_source' => 'test_mmpi',
                    'review_source_label' => 'Hasil Test MMPI',
                    'jenis_review' => 'Hasil Test MMPI',
                    'hasil_interview_id' => null,
                    'hasil_test_zoom_id' => null,
                    'hasil_test_mmpi_id' => $row->hasil_test_mmpi_id,
                    'jadwal_test_mmpi_id' => $row->jadwal_test_mmpi_id,
                    'data_riwayat_diri_id' => $row->data_riwayat_diri_id,

                    'judul_tahapan' => 'Hasil Test MMPI',
                    'tanggal_tahapan' => $row->tanggal_test,
                    'tanggal_tahapan_format' => $this->formatTanggalWaktuIndonesia($row->tanggal_test),

                    'judul_interview' => 'Hasil Test MMPI',
                    'tanggal_interview' => $row->tanggal_test,
                    'tanggal_interview_format' => $this->formatTanggalWaktuIndonesia($row->tanggal_test),

                    'nama_kandidat' => $nama,
                    'email_kandidat' => $kandidat?->email ?? $row->email,
                    'no_wa_kandidat' => $kandidat?->no_wa ?? $row->no_wa,
                    'posisi_label' => $kandidat?->posisi_label ?? $row->nama_posisi,
                    'perusahaan_label' => $kandidat?->perusahaan_label ?? $row->nama_perusahaan,

                    'status_kehadiran' => $this->labelKehadiran($row->status_kehadiran),
                    'hasil_interview' => null,
                    'hasil_test' => $this->normalizeHasilTest($row->hasil_test),
                    'hasil_label' => $this->labelHasilTest($row->hasil_test),
                    'file_hasil_test_mmpi' => $row->file_hasil_test_mmpi ?? null,
                    'file_hasil_test_mmpi_url' => $this->makeFileUrl($row->file_hasil_test_mmpi ?? null),
                    'catatan' => null,
                    'created_at' => $row->review_created_at ?? $row->hasil_test_created_at,
                    'updated_at' => $row->review_updated_at ?? $row->hasil_test_updated_at,

                    'review_management_id' => $row->review_management_id,
                    'review_management' => $row->review_management,
                    'status_review' => $row->status_review,

                    'detail_kandidat' => $kandidat,
                    'tanggal_sort' => $row->tanggal_test ? strtotime((string) $row->tanggal_test) : 0,
                ];
            });
    }

    private function getLatestHasilTestZoomForKandidat(?string $kandidatId): ?array
    {
        if (!$kandidatId || !Schema::hasTable('daftar_hadir_test_zoom')) {
            return null;
        }

        $query = DB::table('daftar_hadir_test_zoom as dhz')
            ->where('dhz.data_riwayat_diri_id', $kandidatId);

        if (Schema::hasTable('jadwal_test_zoom')) {
            $query->leftJoin('jadwal_test_zoom as jtz', 'jtz.id', '=', 'dhz.jadwal_test_zoom_id');
        }

        if (Schema::hasColumn('daftar_hadir_test_zoom', 'deleted_at')) {
            $query->whereNull('dhz.deleted_at');
        }

        if (Schema::hasTable('jadwal_test_zoom') && Schema::hasColumn('jadwal_test_zoom', 'deleted_at')) {
            $query->whereNull('jtz.deleted_at');
        }

        $selects = [
            'dhz.id as hasil_test_zoom_id',
            'dhz.jadwal_test_zoom_id',
            'dhz.data_riwayat_diri_id',
            'dhz.tanggal_kehadiran',
            'dhz.status_kehadiran',
            'dhz.hasil_test',
            Schema::hasColumn('daftar_hadir_test_zoom', 'file_hasil_test_zoom')
                ? 'dhz.file_hasil_test_zoom'
                : DB::raw('NULL as file_hasil_test_zoom'),
            'dhz.created_at as hasil_test_created_at',
            'dhz.updated_at as hasil_test_updated_at',
        ];

        $selects[] = Schema::hasTable('jadwal_test_zoom') && Schema::hasColumn('jadwal_test_zoom', 'jadwal')
            ? 'jtz.jadwal as tanggal_test'
            : DB::raw('dhz.tanggal_kehadiran as tanggal_test');

        foreach (['hasil_test_iq', 'hasil_test_disc', 'hasil_test_eysenck'] as $column) {
            $selects[] = Schema::hasColumn('daftar_hadir_test_zoom', $column)
                ? "dhz.{$column}"
                : DB::raw("NULL as {$column}");
        }

        $row = $query
            ->select($selects)
            ->orderByDesc(DB::raw('COALESCE(' . (Schema::hasTable('jadwal_test_zoom') && Schema::hasColumn('jadwal_test_zoom', 'jadwal') ? 'jtz.jadwal, ' : '') . 'dhz.tanggal_kehadiran, dhz.created_at)'))
            ->first();

        if (!$row) {
            return null;
        }

        return $this->mapLatestTestZoomRow($row);
    }

    private function getLatestHasilTestMmpiForKandidat(?string $kandidatId): ?array
    {
        if (!$kandidatId || !Schema::hasTable('daftar_hadir_test_mmpi')) {
            return null;
        }

        $query = DB::table('daftar_hadir_test_mmpi as dhm')
            ->where('dhm.data_riwayat_diri_id', $kandidatId);

        if (Schema::hasTable('jadwal_test_mmpi')) {
            $query->leftJoin('jadwal_test_mmpi as jtm', 'jtm.id', '=', 'dhm.jadwal_test_mmpi_id');
        }

        if (Schema::hasColumn('daftar_hadir_test_mmpi', 'deleted_at')) {
            $query->whereNull('dhm.deleted_at');
        }

        if (Schema::hasTable('jadwal_test_mmpi') && Schema::hasColumn('jadwal_test_mmpi', 'deleted_at')) {
            $query->whereNull('jtm.deleted_at');
        }

        $selects = [
            'dhm.id as hasil_test_mmpi_id',
            'dhm.jadwal_test_mmpi_id',
            'dhm.data_riwayat_diri_id',
            'dhm.tanggal_kehadiran',
            'dhm.status_kehadiran',
            'dhm.hasil_test',
            Schema::hasColumn('daftar_hadir_test_mmpi', 'file_hasil_test_mmpi')
                ? 'dhm.file_hasil_test_mmpi'
                : DB::raw('NULL as file_hasil_test_mmpi'),
            'dhm.created_at as hasil_test_created_at',
            'dhm.updated_at as hasil_test_updated_at',
        ];

        $selects[] = Schema::hasTable('jadwal_test_mmpi') && Schema::hasColumn('jadwal_test_mmpi', 'tanggal')
            ? 'jtm.tanggal as tanggal_test'
            : DB::raw('dhm.tanggal_kehadiran as tanggal_test');

        $row = $query
            ->select($selects)
            ->orderByDesc(DB::raw('COALESCE(' . (Schema::hasTable('jadwal_test_mmpi') && Schema::hasColumn('jadwal_test_mmpi', 'tanggal') ? 'jtm.tanggal, ' : '') . 'dhm.tanggal_kehadiran, dhm.created_at)'))
            ->first();

        if (!$row) {
            return null;
        }

        return $this->mapLatestTestMmpiRow($row);
    }

    private function mapLatestTestZoomRow($row): array
    {
        $catatanTest = collect([
            'IQ' => $row->hasil_test_iq ?? null,
            'DISC' => $row->hasil_test_disc ?? null,
            'Eysenck' => $row->hasil_test_eysenck ?? null,
        ])
            ->filter(fn ($value) => filled($value))
            ->map(fn ($value, $key) => "{$key}: {$value}")
            ->implode(' | ');

        return [
            'id' => $row->hasil_test_zoom_id,
            'source_id' => $row->hasil_test_zoom_id,
            'review_source' => 'test_zoom',
            'review_source_label' => 'Hasil Test Zoom',
            'jenis_review' => 'Hasil Test Zoom',
            'hasil_test_zoom_id' => $row->hasil_test_zoom_id,
            'jadwal_test_zoom_id' => $row->jadwal_test_zoom_id ?? null,
            'data_riwayat_diri_id' => $row->data_riwayat_diri_id ?? null,
            'judul_tahapan' => 'Hasil Test Zoom',
            'tanggal_tahapan' => $row->tanggal_test ?? $row->tanggal_kehadiran ?? null,
            'tanggal_tahapan_format' => $this->formatTanggalWaktuIndonesia($row->tanggal_test ?? $row->tanggal_kehadiran ?? null),
            'tanggal_test_zoom' => $row->tanggal_test ?? $row->tanggal_kehadiran ?? null,
            'tanggal_test_zoom_format' => $this->formatTanggalWaktuIndonesia($row->tanggal_test ?? $row->tanggal_kehadiran ?? null),
            'status_kehadiran' => $this->labelKehadiran($row->status_kehadiran ?? null),
            'hasil_test' => $this->normalizeHasilTest($row->hasil_test ?? null),
            'hasil_label' => $this->labelHasilTest($row->hasil_test ?? null),
            'file_hasil_test_zoom' => $row->file_hasil_test_zoom ?? null,
            'file_hasil_test_zoom_url' => $this->makeFileUrl($row->file_hasil_test_zoom ?? null),
            'hasil_test_iq' => $row->hasil_test_iq ?? null,
            'hasil_test_disc' => $row->hasil_test_disc ?? null,
            'hasil_test_eysenck' => $row->hasil_test_eysenck ?? null,
            'catatan' => $catatanTest ?: null,
        ];
    }

    private function mapLatestTestMmpiRow($row): array
    {
        return [
            'id' => $row->hasil_test_mmpi_id,
            'source_id' => $row->hasil_test_mmpi_id,
            'review_source' => 'test_mmpi',
            'review_source_label' => 'Hasil Test MMPI',
            'jenis_review' => 'Hasil Test MMPI',
            'hasil_test_mmpi_id' => $row->hasil_test_mmpi_id,
            'jadwal_test_mmpi_id' => $row->jadwal_test_mmpi_id ?? null,
            'data_riwayat_diri_id' => $row->data_riwayat_diri_id ?? null,
            'judul_tahapan' => 'Hasil Test MMPI',
            'tanggal_tahapan' => $row->tanggal_test ?? $row->tanggal_kehadiran ?? null,
            'tanggal_tahapan_format' => $this->formatTanggalWaktuIndonesia($row->tanggal_test ?? $row->tanggal_kehadiran ?? null),
            'tanggal_test_mmpi' => $row->tanggal_test ?? $row->tanggal_kehadiran ?? null,
            'tanggal_test_mmpi_format' => $this->formatTanggalWaktuIndonesia($row->tanggal_test ?? $row->tanggal_kehadiran ?? null),
            'status_kehadiran' => $this->labelKehadiran($row->status_kehadiran ?? null),
            'hasil_test' => $this->normalizeHasilTest($row->hasil_test ?? null),
            'hasil_label' => $this->labelHasilTest($row->hasil_test ?? null),
            'file_hasil_test_mmpi' => $row->file_hasil_test_mmpi ?? null,
            'file_hasil_test_mmpi_url' => $this->makeFileUrl($row->file_hasil_test_mmpi ?? null),
            'catatan' => null,
        ];
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'review_source' => [
                'nullable',
                'string',
                Rule::in($this->reviewSourceOptions),
            ],
            'hasil_interview_id' => [
                'nullable',
                'uuid',
                'exists:jadwal_interview_kandidat,id',
            ],
            'hasil_test_zoom_id' => [
                'nullable',
                'uuid',
                'exists:daftar_hadir_test_zoom,id',
            ],
            'hasil_test_mmpi_id' => [
                'nullable',
                'uuid',
                'exists:daftar_hadir_test_mmpi,id',
            ],
            'review_management' => [
                'nullable',
                'string',
            ],
            'status' => [
                'nullable',
                'string',
                Rule::in($this->statusReviewOptions),
            ],
        ]);

        $reviewSource = $validated['review_source'] ?? null;

        if (!$reviewSource) {
            $reviewSource = !empty($validated['hasil_test_zoom_id'])
                ? 'test_zoom'
                : (!empty($validated['hasil_test_mmpi_id']) ? 'test_mmpi' : 'interview');
        }

        if ($reviewSource === 'test_zoom') {
            return $this->storeReviewHasilTestZoom($validated);
        }

        if ($reviewSource === 'test_mmpi') {
            return $this->storeReviewHasilTestMmpi($validated);
        }

        return $this->storeReviewInterview($validated);
    }

    private function storeReviewInterview(array $validated): JsonResponse
    {
        if (empty($validated['hasil_interview_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'ID hasil interview wajib dikirim untuk review interview.',
            ], 422);
        }

        $jadwalKandidat = JadwalInterviewKandidat::query()
            ->where('id', $validated['hasil_interview_id'])
            ->whereIn('hasil_interview', $this->hasilInterviewReviewOptions)
            ->first();

        if (!$jadwalKandidat) {
            return response()->json([
                'success' => false,
                'message' => 'Data hanya bisa direview jika hasil interview Lolos Interview atau Dipertimbangkan.',
            ], 422);
        }

        $review = HasilReviewManagement::query()
            ->where('hasil_interview_id', $validated['hasil_interview_id'])
            ->first();

        if (!$review) {
            $review = new HasilReviewManagement();
            $review->id = (string) Str::uuid();
            $review->hasil_interview_id = $validated['hasil_interview_id'];
        }

        if (Schema::hasColumn('hasil_review_management', 'sumber_review')) {
            $review->sumber_review = 'interview';
        }

        if (Schema::hasColumn('hasil_review_management', 'daftar_hadir_test_zoom_id')) {
            $review->daftar_hadir_test_zoom_id = null;
        }

        if (Schema::hasColumn('hasil_review_management', 'daftar_hadir_test_mmpi_id')) {
            $review->daftar_hadir_test_mmpi_id = null;
        }

        $review->review_management = $validated['review_management'] ?? null;
        $review->status = $validated['status'] ?? null;
        $review->save();

        return response()->json([
            'success' => true,
            'message' => 'Review management interview berhasil disimpan.',
            'data' => $review,
        ]);
    }

    private function storeReviewHasilTestZoom(array $validated): JsonResponse
    {
        if (!Schema::hasColumn('hasil_review_management', 'daftar_hadir_test_zoom_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Kolom daftar_hadir_test_zoom_id belum tersedia di tabel hasil_review_management. Jalankan migration terlebih dahulu.',
            ], 500);
        }

        if (empty($validated['hasil_test_zoom_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'ID hasil test Zoom wajib dikirim untuk review hasil test.',
            ], 422);
        }

        $hasilTest = DB::table('daftar_hadir_test_zoom')
            ->where('id', $validated['hasil_test_zoom_id'])
            ->first();

        if (!$hasilTest || $this->normalizeHasilTest($hasilTest->hasil_test ?? null) !== 'lolos') {
            return response()->json([
                'success' => false,
                'message' => 'Data hanya bisa direview jika hasil test Zoom Lolos.',
            ], 422);
        }

        $existing = DB::table('hasil_review_management')
            ->where('daftar_hadir_test_zoom_id', $validated['hasil_test_zoom_id'])
            ->first();

        $payload = [
            'hasil_interview_id' => null,
            'daftar_hadir_test_zoom_id' => $validated['hasil_test_zoom_id'],
            'review_management' => $validated['review_management'] ?? null,
            'status' => $validated['status'] ?? null,
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('hasil_review_management', 'daftar_hadir_test_mmpi_id')) {
            $payload['daftar_hadir_test_mmpi_id'] = null;
        }

        if (Schema::hasColumn('hasil_review_management', 'sumber_review')) {
            $payload['sumber_review'] = 'test_zoom';
        }

        if ($existing) {
            DB::table('hasil_review_management')
                ->where('id', $existing->id)
                ->update($payload);

            $reviewId = $existing->id;
        } else {
            $reviewId = (string) Str::uuid();

            DB::table('hasil_review_management')->insert(array_merge($payload, [
                'id' => $reviewId,
                'created_at' => now(),
            ]));
        }

        $review = HasilReviewManagement::query()->find($reviewId);

        return response()->json([
            'success' => true,
            'message' => 'Review management hasil test Zoom berhasil disimpan.',
            'data' => $review,
        ]);
    }

    private function storeReviewHasilTestMmpi(array $validated): JsonResponse
    {
        if (!Schema::hasColumn('hasil_review_management', 'daftar_hadir_test_mmpi_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Kolom daftar_hadir_test_mmpi_id belum tersedia di tabel hasil_review_management. Jalankan migration terlebih dahulu.',
            ], 500);
        }

        if (empty($validated['hasil_test_mmpi_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'ID hasil test MMPI wajib dikirim untuk review hasil test.',
            ], 422);
        }

        $hasilTest = DB::table('daftar_hadir_test_mmpi')
            ->where('id', $validated['hasil_test_mmpi_id'])
            ->first();

        if (!$hasilTest || $this->normalizeHasilTest($hasilTest->hasil_test ?? null) !== 'lolos') {
            return response()->json([
                'success' => false,
                'message' => 'Data hanya bisa direview jika hasil test MMPI Lolos.',
            ], 422);
        }

        $existing = DB::table('hasil_review_management')
            ->where('daftar_hadir_test_mmpi_id', $validated['hasil_test_mmpi_id'])
            ->first();

        $payload = [
            'hasil_interview_id' => null,
            'daftar_hadir_test_mmpi_id' => $validated['hasil_test_mmpi_id'],
            'review_management' => $validated['review_management'] ?? null,
            'status' => $validated['status'] ?? null,
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('hasil_review_management', 'daftar_hadir_test_zoom_id')) {
            $payload['daftar_hadir_test_zoom_id'] = null;
        }

        if (Schema::hasColumn('hasil_review_management', 'sumber_review')) {
            $payload['sumber_review'] = 'test_mmpi';
        }

        if ($existing) {
            DB::table('hasil_review_management')
                ->where('id', $existing->id)
                ->update($payload);

            $reviewId = $existing->id;
        } else {
            $reviewId = (string) Str::uuid();

            DB::table('hasil_review_management')->insert(array_merge($payload, [
                'id' => $reviewId,
                'created_at' => now(),
            ]));
        }

        $review = HasilReviewManagement::query()->find($reviewId);

        return response()->json([
            'success' => true,
            'message' => 'Review management hasil test MMPI berhasil disimpan.',
            'data' => $review,
        ]);
    }

    public function show(HasilReviewManagement $hasilReviewManagement): JsonResponse
    {
        $hasilReviewManagement->load([
            'hasilInterview.jadwalInterview:id,judul_interview,jadwal_interview',
            'hasilInterview.kandidat' => function ($query) {
                $query->with($this->safePelamarRelations());
            },
        ]);

        if ($hasilReviewManagement->hasilInterview?->kandidat) {
            $hasilReviewManagement->hasilInterview->kandidat =
                $this->appendExtraData($hasilReviewManagement->hasilInterview->kandidat);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail review management berhasil diambil.',
            'data' => $hasilReviewManagement,
        ]);
    }

    public function update(Request $request, HasilReviewManagement $hasilReviewManagement): JsonResponse
    {
        $validated = $request->validate([
            'review_management' => [
                'nullable',
                'string',
            ],
            'status' => [
                'nullable',
                'string',
                Rule::in($this->statusReviewOptions),
            ],
        ]);

        $hasilReviewManagement->update([
            'review_management' => $validated['review_management'] ?? null,
            'status' => $validated['status'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review management berhasil diperbarui.',
            'data' => $hasilReviewManagement,
        ]);
    }

    public function destroy(HasilReviewManagement $hasilReviewManagement): JsonResponse
    {
        $hasilReviewManagement->update([
            'review_management' => null,
            'status' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review management berhasil dikosongkan.',
        ]);
    }

    private function normalizeHasilTest($value): ?string
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

    private function labelHasilTest($value): string
    {
        return match ($this->normalizeHasilTest($value)) {
            'lolos' => 'Lolos',
            'gagal' => 'Tidak Lolos',
            default => 'Belum Ada',
        };
    }

    private function labelKehadiran($value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        $normalized = strtolower(trim((string) $value));
        $normalized = str_replace([' ', '-'], '_', $normalized);

        return match ($normalized) {
            'hadir', '1', 'true', 'ya', 'yes' => 'Hadir',
            'tidak_hadir', 'tidakhadir', 'tidak', '0', 'false', 'no' => 'Tidak Hadir',
            default => (string) $value,
        };
    }

    private function formatTanggalWaktuIndonesia($value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value)->locale('id')->translatedFormat('d F Y H:i');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }

    private function makeFileUrl(?string $path): ?string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $path)) {
            return $path;
        }

        if (str_starts_with($path, '/storage/')) {
            return $path;
        }

        if (str_starts_with($path, 'storage/')) {
            return asset($path);
        }

        return asset('storage/' . ltrim($path, '/'));
    }

    private function makePendaftaranUrl(?string $token): ?string
    {
        if (!$token) {
            return null;
        }

        return route('pendaftaran.show', [
            'token' => $token,
        ]);
    }

    private function appendExtraData(DataRiwayatDiri $data): DataRiwayatDiri
    {
        $data->pendaftaran_url = $this->makePendaftaranUrl($data->token ?? null);

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

    private function safePelamarRelations(): array
    {
        return $this->pelamarRelations;
    }
}