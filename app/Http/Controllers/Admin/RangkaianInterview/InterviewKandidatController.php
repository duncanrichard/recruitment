<?php

namespace App\Http\Controllers\Admin\RangkaianInterview;

use App\Http\Controllers\Controller;
use App\Models\HasilReviewManagement;
use App\Models\JadwalInterview;
use App\Models\JadwalInterviewKandidat;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class InterviewKandidatController extends Controller
{
    private array $statusKehadiranOptions = [
        'Hadir',
        'Tidak Hadir',
        'Tidak Respon',
        'Reschedule',
    ];

    private array $statusKehadiranAktifOptions = [
        'Hadir',
        'Tidak Hadir',
        'Tidak Respon',
    ];

    private array $hasilInterviewOptions = [
        'Lolos Interview',
        'Tidak Lolos Interview',
        'Dipertimbangkan',
    ];

    private array $hasilInterviewMasukReviewManagement = [
        'Lolos Interview',
        'Dipertimbangkan',
    ];

    public function list(Request $request)
    {
        $validated = $request->validate([
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
        ]);

        $tanggalMulai = $validated['tanggal_mulai'] ?? now()->toDateString();
        $tanggalSelesai = $validated['tanggal_selesai'] ?? now()->toDateString();

        $rows = JadwalInterviewKandidat::query()
            ->with([
                'jadwalInterview:id,judul_interview,jadwal_interview,deleted_at',
                'kandidat:id,nama_lengkap,nama_panggil,email,no_wa,posisi_yang_dilamar',
            ])
            ->whereHas('jadwalInterview', function ($query) use ($tanggalMulai, $tanggalSelesai) {
                $query
                    ->whereNull('deleted_at')
                    ->whereDate('jadwal_interview', '>=', $tanggalMulai)
                    ->whereDate('jadwal_interview', '<=', $tanggalSelesai);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $data = $rows
            ->groupBy('jadwal_interview_id')
            ->map(function ($items) {
                $first = $items->first();

                return [
                    'id' => $first->jadwal_interview_id,
                    'jadwal_interview_id' => $first->jadwal_interview_id,
                    'jadwal_interview' => $first->jadwalInterview,
                    'kandidat_ids' => $items->pluck('data_riwayat_diri_id')->values(),
                    'kandidats' => $items->map(function ($item) {
                        return $this->mapKandidat($item);
                    })->values(),
                    'jumlah_kandidat' => $items->count(),
                    'created_at' => $items->min('created_at'),
                    'updated_at' => $items->max('updated_at'),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Data kandidat interview berhasil diambil.',
            'filter' => [
                'tanggal_mulai' => $tanggalMulai,
                'tanggal_selesai' => $tanggalSelesai,
            ],
            'data' => $data,
        ]);
    }

    public function detail(string $jadwalInterviewId)
    {
        $items = JadwalInterviewKandidat::query()
            ->with([
                'jadwalInterview:id,judul_interview,jadwal_interview,deleted_at',
                'kandidat:id,nama_lengkap,nama_panggil,email,no_wa,posisi_yang_dilamar',
            ])
            ->where('jadwal_interview_id', $jadwalInterviewId)
            ->whereHas('jadwalInterview', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->orderBy('created_at', 'asc')
            ->get();

        if ($items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Data kandidat pada jadwal ini tidak ditemukan.',
            ], 404);
        }

        $first = $items->first();

        return response()->json([
            'success' => true,
            'message' => 'Detail kandidat interview berhasil diambil.',
            'data' => [
                'id' => $first->jadwal_interview_id,
                'jadwal_interview_id' => $first->jadwal_interview_id,
                'jadwal_interview' => $first->jadwalInterview,
                'kandidat_ids' => $items->pluck('data_riwayat_diri_id')->values(),
                'kandidats' => $items->map(function ($item) {
                    return $this->mapKandidat($item);
                })->values(),
                'jumlah_kandidat' => $items->count(),
                'created_at' => $items->min('created_at'),
                'updated_at' => $items->max('updated_at'),
            ],
        ]);
    }

    public function jadwalOptions(Request $request)
    {
        $includeJadwalInterviewId = $request->query('include_jadwal_interview_id');

        $data = JadwalInterview::query()
            ->select('id', 'judul_interview', 'jadwal_interview')
            ->whereNull('deleted_at')
            ->where(function ($query) use ($includeJadwalInterviewId) {
                $query->where('jadwal_interview', '>=', now());

                if (!empty($includeJadwalInterviewId)) {
                    $query->orWhere('id', $includeJadwalInterviewId);
                }
            })
            ->orderBy('jadwal_interview', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data jadwal interview berhasil diambil.',
            'data' => $data,
        ]);
    }

    public function kandidatOptions(Request $request)
    {
        $includeJadwalInterviewId = $request->query('include_jadwal_interview_id');

        $query = DB::table('data_riwayat_diri')
            ->join(
                'daftar_hadir_test_mmpi',
                'daftar_hadir_test_mmpi.data_riwayat_diri_id',
                '=',
                'data_riwayat_diri.id'
            )
            ->whereNull('daftar_hadir_test_mmpi.deleted_at')
            ->whereRaw("LOWER(TRIM(COALESCE(daftar_hadir_test_mmpi.hasil_test, ''))) = ?", ['lolos'])
            ->whereNotExists(function ($subQuery) use ($includeJadwalInterviewId) {
                $subQuery->select(DB::raw(1))
                    ->from('jadwal_interview_kandidat as jik')
                    ->join('jadwal_interview as ji', function ($join) {
                        $join
                            ->on('ji.id', '=', 'jik.jadwal_interview_id')
                            ->whereNull('ji.deleted_at');
                    })
                    ->whereColumn('jik.data_riwayat_diri_id', 'data_riwayat_diri.id')
                    ->where(function ($statusQuery) {
                        $statusQuery
                            ->whereNull('jik.status_kehadiran')
                            ->orWhereIn('jik.status_kehadiran', $this->statusKehadiranAktifOptions);
                    });

                if (!empty($includeJadwalInterviewId)) {
                    $subQuery->where('jik.jadwal_interview_id', '!=', $includeJadwalInterviewId);
                }
            });

        if (Schema::hasColumn('data_riwayat_diri', 'deleted_at')) {
            $query->whereNull('data_riwayat_diri.deleted_at');
        }

        $data = $query
            ->select([
                'data_riwayat_diri.id',
                'data_riwayat_diri.nama_lengkap',
                'data_riwayat_diri.nama_panggil',
                'data_riwayat_diri.email',
                'data_riwayat_diri.no_wa',
                'data_riwayat_diri.posisi_yang_dilamar',
                'daftar_hadir_test_mmpi.hasil_test',
            ])
            ->distinct()
            ->orderBy('data_riwayat_diri.nama_lengkap', 'asc')
            ->get()
            ->map(function ($item) {
                $item->posisi_dilamar = $this->getNamaPosisi($item->posisi_yang_dilamar ?? null);

                return $item;
            });

        return response()->json([
            'success' => true,
            'message' => 'Data kandidat berhasil diambil.',
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jadwal_interview_id' => [
                'required',
                'uuid',
                Rule::exists('jadwal_interview', 'id')->where(function ($query) {
                    $query->whereNull('deleted_at');
                }),
            ],
            'kandidat_ids' => ['required', 'array', 'min:1'],
            'kandidat_ids.*' => ['required', 'uuid', 'exists:data_riwayat_diri,id'],
        ]);

        $this->validateJadwalInterviewBelumLewat($validated['jadwal_interview_id']);

        $kandidatIds = collect($validated['kandidat_ids'])->unique()->values();

        $this->validateKandidatLolosMmpi($kandidatIds->all());
        $this->validateKandidatBelumPunyaJadwalAktif($kandidatIds->all());

        DB::transaction(function () use ($validated, $kandidatIds) {
            $existingKandidatIds = JadwalInterviewKandidat::query()
                ->where('jadwal_interview_id', $validated['jadwal_interview_id'])
                ->whereIn('data_riwayat_diri_id', $kandidatIds)
                ->pluck('data_riwayat_diri_id');

            $newKandidatIds = $kandidatIds->diff($existingKandidatIds)->values();

            if ($newKandidatIds->isEmpty()) {
                return;
            }

            $rows = $newKandidatIds->map(function ($kandidatId) use ($validated) {
                return [
                    'id' => (string) Str::uuid(),
                    'jadwal_interview_id' => $validated['jadwal_interview_id'],
                    'data_riwayat_diri_id' => $kandidatId,
                    'status_kehadiran' => null,
                    'hasil_interview' => null,
                    'catatan' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->all();

            DB::table('jadwal_interview_kandidat')->insert($rows);
        });

        return response()->json([
            'success' => true,
            'message' => 'Kandidat berhasil dimasukkan ke jadwal interview.',
        ]);
    }

    public function update(Request $request, string $jadwalInterviewId)
    {
        $validated = $request->validate([
            'jadwal_interview_id' => [
                'required',
                'uuid',
                Rule::exists('jadwal_interview', 'id')->where(function ($query) {
                    $query->whereNull('deleted_at');
                }),
            ],
            'kandidat_ids' => ['required', 'array', 'min:1'],
            'kandidat_ids.*' => ['required', 'uuid', 'exists:data_riwayat_diri,id'],
        ]);

        if ((string) $validated['jadwal_interview_id'] !== (string) $jadwalInterviewId) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal interview tidak sesuai.',
            ], 422);
        }

        $this->validateJadwalInterviewBelumLewat($jadwalInterviewId);

        $kandidatIds = collect($validated['kandidat_ids'])->unique()->values();

        $this->validateKandidatLolosMmpi($kandidatIds->all());
        $this->validateKandidatBelumPunyaJadwalAktif($kandidatIds->all(), $jadwalInterviewId);

        DB::transaction(function () use ($jadwalInterviewId, $kandidatIds) {
            $deletedIds = JadwalInterviewKandidat::query()
                ->where('jadwal_interview_id', $jadwalInterviewId)
                ->whereNotIn('data_riwayat_diri_id', $kandidatIds)
                ->pluck('id');

            if ($deletedIds->isNotEmpty()) {
                HasilReviewManagement::query()
                    ->whereIn('hasil_interview_id', $deletedIds)
                    ->delete();

                JadwalInterviewKandidat::query()
                    ->whereIn('id', $deletedIds)
                    ->delete();
            }

            $existingKandidatIds = JadwalInterviewKandidat::query()
                ->where('jadwal_interview_id', $jadwalInterviewId)
                ->pluck('data_riwayat_diri_id');

            $newKandidatIds = $kandidatIds->diff($existingKandidatIds)->values();

            if ($newKandidatIds->isNotEmpty()) {
                $rows = $newKandidatIds->map(function ($kandidatId) use ($jadwalInterviewId) {
                    return [
                        'id' => (string) Str::uuid(),
                        'jadwal_interview_id' => $jadwalInterviewId,
                        'data_riwayat_diri_id' => $kandidatId,
                        'status_kehadiran' => null,
                        'hasil_interview' => null,
                        'catatan' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })->all();

                DB::table('jadwal_interview_kandidat')->insert($rows);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Kandidat jadwal interview berhasil diperbarui.',
        ]);
    }

    public function updateTanggal(Request $request, string $jadwalInterviewId)
    {
        $validated = $request->validate([
            'jadwal_interview' => ['required', 'date'],
        ]);

        $jadwal = JadwalInterview::query()
            ->whereNull('deleted_at')
            ->findOrFail($jadwalInterviewId);

        $jadwal->update([
            'jadwal_interview' => $validated['jadwal_interview'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tanggal interview berhasil diperbarui.',
        ]);
    }

    public function updateHasil(Request $request, string $jadwalInterviewId, string $pivotId)
    {
        $validated = $request->validate([
            'status_kehadiran' => ['nullable', Rule::in($this->statusKehadiranOptions)],
            'hasil_interview' => ['nullable', Rule::in($this->hasilInterviewOptions)],
            'catatan' => ['nullable', 'string', 'max:2000'],
        ]);

        $row = JadwalInterviewKandidat::query()
            ->where('jadwal_interview_id', $jadwalInterviewId)
            ->where('id', $pivotId)
            ->whereHas('jadwalInterview', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->firstOrFail();

        DB::transaction(function () use ($request, $validated, $row) {
            $payload = [];

            if ($request->has('status_kehadiran')) {
                $payload['status_kehadiran'] = $validated['status_kehadiran'] ?? null;

                if (
                    empty($validated['status_kehadiran']) ||
                    $validated['status_kehadiran'] === 'Reschedule'
                ) {
                    $payload['hasil_interview'] = null;
                }
            }

            if ($request->has('hasil_interview')) {
                $payload['hasil_interview'] = $validated['hasil_interview'] ?? null;
            }

            if ($request->has('catatan')) {
                $payload['catatan'] = $validated['catatan'] ?? null;
            }

            if (!empty($payload)) {
                $row->update($payload);
                $row->refresh();
            }

            $this->syncHasilReviewManagement($row);
        });

        return response()->json([
            'success' => true,
            'message' => 'Data kandidat berhasil diperbarui.',
        ]);
    }

    public function destroy(string $jadwalInterviewId)
    {
        DB::transaction(function () use ($jadwalInterviewId) {
            $pivotIds = JadwalInterviewKandidat::query()
                ->where('jadwal_interview_id', $jadwalInterviewId)
                ->pluck('id');

            if ($pivotIds->isNotEmpty()) {
                HasilReviewManagement::query()
                    ->whereIn('hasil_interview_id', $pivotIds)
                    ->delete();

                JadwalInterviewKandidat::query()
                    ->whereIn('id', $pivotIds)
                    ->delete();
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Kandidat jadwal interview berhasil dihapus.',
        ]);
    }

    public function destroyKandidat(string $jadwalInterviewId, string $pivotId)
    {
        DB::transaction(function () use ($jadwalInterviewId, $pivotId) {
            $row = JadwalInterviewKandidat::query()
                ->where('jadwal_interview_id', $jadwalInterviewId)
                ->where('id', $pivotId)
                ->firstOrFail();

            HasilReviewManagement::query()
                ->where('hasil_interview_id', $row->id)
                ->delete();

            $row->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Kandidat berhasil dihapus dari jadwal interview.',
        ]);
    }

    private function syncHasilReviewManagement(JadwalInterviewKandidat $row): void
    {
        $hasilInterview = trim((string) $row->hasil_interview);

        if (in_array($hasilInterview, $this->hasilInterviewMasukReviewManagement, true)) {
            HasilReviewManagement::query()->updateOrCreate(
                [
                    'hasil_interview_id' => $row->id,
                ],
                [
                    'review_management' => null,
                    'status' => null,
                ]
            );

            return;
        }

        HasilReviewManagement::query()
            ->where('hasil_interview_id', $row->id)
            ->delete();
    }

    private function validateJadwalInterviewBelumLewat(string $jadwalInterviewId): void
    {
        $jadwal = JadwalInterview::query()
            ->select('id', 'jadwal_interview')
            ->whereNull('deleted_at')
            ->where('id', $jadwalInterviewId)
            ->first();

        if (!$jadwal) {
            abort(response()->json([
                'success' => false,
                'message' => 'Jadwal interview tidak ditemukan atau sudah dihapus.',
            ], 404));
        }

        if (empty($jadwal->jadwal_interview)) {
            abort(response()->json([
                'success' => false,
                'message' => 'Tanggal interview belum diisi.',
            ], 422));
        }

        $tanggalInterview = Carbon::parse($jadwal->jadwal_interview);

        if ($tanggalInterview->lt(now())) {
            abort(response()->json([
                'success' => false,
                'message' => 'Jadwal interview sudah melewati tanggal sekarang.',
            ], 422));
        }
    }

    private function validateKandidatBelumPunyaJadwalAktif(array $kandidatIds, ?string $excludeJadwalInterviewId = null): void
    {
        $query = JadwalInterviewKandidat::query()
            ->whereIn('data_riwayat_diri_id', $kandidatIds)
            ->whereHas('jadwalInterview', function ($jadwalQuery) {
                $jadwalQuery->whereNull('deleted_at');
            })
            ->where(function ($statusQuery) {
                $statusQuery
                    ->whereNull('status_kehadiran')
                    ->orWhereIn('status_kehadiran', $this->statusKehadiranAktifOptions);
            });

        if (!empty($excludeJadwalInterviewId)) {
            $query->where('jadwal_interview_id', '!=', $excludeJadwalInterviewId);
        }

        if ($query->exists()) {
            abort(response()->json([
                'success' => false,
                'message' => 'Ada kandidat yang masih memiliki jadwal interview aktif. Kandidat hanya bisa ditambahkan ulang jika statusnya Reschedule.',
            ], 422));
        }
    }

    private function validateKandidatLolosMmpi(array $kandidatIds): void
    {
        $validIds = DB::table('data_riwayat_diri')
            ->join(
                'daftar_hadir_test_mmpi',
                'daftar_hadir_test_mmpi.data_riwayat_diri_id',
                '=',
                'data_riwayat_diri.id'
            )
            ->whereNull('daftar_hadir_test_mmpi.deleted_at')
            ->whereIn('data_riwayat_diri.id', $kandidatIds)
            ->whereRaw("LOWER(TRIM(COALESCE(daftar_hadir_test_mmpi.hasil_test, ''))) = ?", ['lolos'])
            ->pluck('data_riwayat_diri.id')
            ->unique()
            ->values();

        if ($validIds->count() !== count($kandidatIds)) {
            abort(response()->json([
                'success' => false,
                'message' => 'Ada kandidat yang belum memiliki hasil MMPI lolos.',
            ], 422));
        }
    }

    private function mapKandidat(JadwalInterviewKandidat $item): array
    {
        $kandidat = $item->kandidat;
        $posisiRaw = $kandidat?->posisi_yang_dilamar;

        return [
            'pivot_id' => $item->id,
            'id' => $kandidat?->id,
            'nama_lengkap' => $kandidat?->nama_lengkap,
            'nama_panggil' => $kandidat?->nama_panggil,
            'email' => $kandidat?->email,
            'no_wa' => $kandidat?->no_wa,
            'posisi_yang_dilamar' => $posisiRaw,
            'posisi_dilamar' => $this->getNamaPosisi($posisiRaw),
            'status_kehadiran' => $item->status_kehadiran,
            'hasil_interview' => $item->hasil_interview,
            'catatan' => $item->catatan,
        ];
    }

    private function getNamaPosisi($posisiId): string
    {
        if (empty($posisiId)) {
            return '-';
        }

        if (!Schema::hasTable('posisi')) {
            return (string) $posisiId;
        }

        if (!Schema::hasColumn('posisi', 'id')) {
            return (string) $posisiId;
        }

        $posisi = DB::table('posisi')
            ->where('id', $posisiId)
            ->first();

        if (!$posisi) {
            return (string) $posisiId;
        }

        foreach ([
            'nama_posisi',
            'posisi',
            'nama',
            'nama_jabatan',
            'jabatan',
        ] as $column) {
            if (isset($posisi->{$column}) && !empty($posisi->{$column})) {
                return $posisi->{$column};
            }
        }

        return (string) $posisiId;
    }
}