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
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class ReviewManagementController extends Controller
{
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

    public function list(): JsonResponse
    {
        $data = JadwalInterviewKandidat::query()
            ->with([
                'reviewManagement',
                'kandidat' => function ($query) {
                    $query->with($this->safePelamarRelations());
                },
            ])
            ->whereIn('hasil_interview', [
                'Lolos Interview',
                'Dipertimbangkan',
            ])
            ->latest()
            ->get()
            ->map(function ($item) {
                $kandidat = $item->kandidat;

                if ($kandidat) {
                    $kandidat = $this->appendExtraData($kandidat);
                }

                return [
                    'id' => $item->id,
                    'jadwal_interview_id' => $item->jadwal_interview_id,
                    'data_riwayat_diri_id' => $item->data_riwayat_diri_id,

                    'nama_kandidat' => $kandidat?->nama_lengkap ?? '-',
                    'email_kandidat' => $kandidat?->email,
                    'no_wa_kandidat' => $kandidat?->no_wa,
                    'posisi_label' => $kandidat?->posisi_label,
                    'perusahaan_label' => $kandidat?->perusahaan_label,

                    'status_kehadiran' => $item->status_kehadiran,
                    'hasil_interview' => $item->hasil_interview,
                    'catatan' => $item->catatan,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,

                    'review_management_id' => $item->reviewManagement?->id,
                    'review_management' => $item->reviewManagement?->review_management,
                    'status_review' => $item->reviewManagement?->status,

                    'detail_kandidat' => $kandidat,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Data review management berhasil diambil.',
            'data' => $data,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'hasil_interview_id' => [
                'required',
                'uuid',
                'exists:jadwal_interview_kandidat,id',
            ],
            'review_management' => [
                'nullable',
                'string',
            ],
            'status' => [
                'required',
                'string',
                Rule::in(['Diterima', 'Gagal']),
            ],
        ]);

        $jadwalKandidat = JadwalInterviewKandidat::query()
            ->where('id', $validated['hasil_interview_id'])
            ->whereIn('hasil_interview', [
                'Lolos Interview',
                'Dipertimbangkan',
            ])
            ->first();

        if (!$jadwalKandidat) {
            return response()->json([
                'success' => false,
                'message' => 'Data hanya bisa direview jika hasil interview Lolos Interview atau Dipertimbangkan.',
            ], 422);
        }

        $review = HasilReviewManagement::query()->updateOrCreate(
            [
                'hasil_interview_id' => $validated['hasil_interview_id'],
            ],
            [
                'review_management' => $validated['review_management'] ?? null,
                'status' => $validated['status'],
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Review management berhasil disimpan.',
            'data' => $review,
        ]);
    }

    public function show(HasilReviewManagement $hasilReviewManagement): JsonResponse
    {
        $hasilReviewManagement->load([
            'hasilInterview.reviewManagement',
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
                'required',
                'string',
                Rule::in(['Diterima', 'Gagal']),
            ],
        ]);

        $hasilReviewManagement->update([
            'review_management' => $validated['review_management'] ?? null,
            'status' => $validated['status'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review management berhasil diperbarui.',
            'data' => $hasilReviewManagement,
        ]);
    }

    public function destroy(HasilReviewManagement $hasilReviewManagement): JsonResponse
    {
        $hasilReviewManagement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review management berhasil dihapus.',
        ]);
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