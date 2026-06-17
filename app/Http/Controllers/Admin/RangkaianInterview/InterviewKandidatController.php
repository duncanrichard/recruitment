<?php

namespace App\Http\Controllers\Admin\RangkaianInterview;

use App\Http\Controllers\Controller;
use App\Models\HasilReviewManagement;
use App\Models\JadwalInterview;
use App\Models\JadwalInterviewKandidat;
use App\Models\JadwalInterviewPanelis;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Google\Client as GoogleClient;
use Google\Service\Calendar;
use Google\Service\Calendar\ConferenceData;
use Google\Service\Calendar\ConferenceSolutionKey;
use Google\Service\Calendar\CreateConferenceRequest;
use Google\Service\Calendar\Event as GoogleCalendarEvent;
use Google\Service\Calendar\EventAttendee;
use Google\Service\Calendar\EventDateTime;
use Google\Service\Exception as GoogleServiceException;

class InterviewKandidatController extends Controller
{
    private string $timezone = 'Asia/Jakarta';

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

    private function normalizeDateTime(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        return Carbon::parse($value, $this->timezone)->format('Y-m-d H:i:s');
    }

    private function formatDateTimeForJson($value): ?string
    {
        if (!$value) {
            return null;
        }

        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    private function formatDateTimeForDisplay($value): string
    {
        if (!$value) {
            return '-';
        }

        return Carbon::parse($value)
            ->locale('id')
            ->translatedFormat('d F Y H:i');
    }

    private function mapJadwalInterviewForJson(?JadwalInterview $jadwal): ?array
    {
        if (!$jadwal) {
            return null;
        }

        return [
            'id' => $jadwal->id,
            'judul_interview' => $jadwal->judul_interview,
            'jadwal_interview' => $this->formatDateTimeForJson($jadwal->jadwal_interview),
            'google_calendar_event_id' => $this->getJadwalInterviewAttribute($jadwal, 'google_calendar_event_id'),
            'google_calendar_html_link' => $this->getJadwalInterviewAttribute($jadwal, 'google_calendar_html_link'),
            'google_meet_link' => $this->getJadwalInterviewAttribute($jadwal, 'google_meet_link'),
            'deleted_at' => $jadwal->deleted_at
                ? $this->formatDateTimeForJson($jadwal->deleted_at)
                : null,
        ];
    }

    private function mapGroupInterview($items): array
    {
        $first = $items->first();

        return [
            'id' => $first->jadwal_interview_id,
            'jadwal_interview_id' => $first->jadwal_interview_id,
            'jadwal_interview' => $this->mapJadwalInterviewForJson($first->jadwalInterview),
            'kandidat_ids' => $items->pluck('data_riwayat_diri_id')->values(),
            'kandidats' => $items->map(function ($item) {
                return $this->mapKandidat($item);
            })->values(),
            'jumlah_kandidat' => $items->count(),
            'created_at' => $this->formatDateTimeForJson($items->min('created_at')),
            'updated_at' => $this->formatDateTimeForJson($items->max('updated_at')),
        ];
    }

    public function list(Request $request)
    {
        $validated = $request->validate([
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
        ]);

        $tanggalMulai = $validated['tanggal_mulai'] ?? now($this->timezone)->toDateString();
        $tanggalSelesai = $validated['tanggal_selesai'] ?? now($this->timezone)->toDateString();

        $rows = JadwalInterviewKandidat::query()
            ->with([
                'jadwalInterview' => function ($query) {
                    $query->select($this->jadwalInterviewSelectColumns());
                },
                'kandidat:id,nama_lengkap,nama_panggil,email,no_wa,posisi_yang_dilamar,perusahaan_dilamar',
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
                return $this->mapGroupInterview($items);
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
                'jadwalInterview' => function ($query) {
                    $query->select($this->jadwalInterviewSelectColumns());
                },
                'kandidat:id,nama_lengkap,nama_panggil,email,no_wa,posisi_yang_dilamar,perusahaan_dilamar',
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

        return response()->json([
            'success' => true,
            'message' => 'Detail kandidat interview berhasil diambil.',
            'data' => $this->mapGroupInterview($items),
        ]);
    }

    public function jadwalOptions(Request $request)
    {
        $includeJadwalInterviewId = $request->query('include_jadwal_interview_id');

        $data = JadwalInterview::query()
            ->select('id', 'judul_interview', 'jadwal_interview')
            ->whereNull('deleted_at')
            ->where(function ($query) use ($includeJadwalInterviewId) {
                $query->where('jadwal_interview', '>=', now($this->timezone)->format('Y-m-d H:i:s'));

                if (!empty($includeJadwalInterviewId)) {
                    $query->orWhere('id', $includeJadwalInterviewId);
                }
            })
            ->orderBy('jadwal_interview', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'judul_interview' => $item->judul_interview,
                    'jadwal_interview' => $this->formatDateTimeForJson($item->jadwal_interview),
                ];
            })
            ->values();

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
                'data_riwayat_diri.perusahaan_dilamar',
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
                    'created_at' => now($this->timezone),
                    'updated_at' => now($this->timezone),
                ];
            })->all();

            DB::table('jadwal_interview_kandidat')->insert($rows);
        });

        $waResult = $this->kirimPesanJadwalInterviewKePanelis(
            $validated['jadwal_interview_id'],
            $kandidatIds->all()
        );

        $calendarResult = $this->syncGoogleCalendarInterview(
            $validated['jadwal_interview_id'],
            $kandidatIds->all()
        );

        return response()->json([
            'success' => true,
            'message' => 'Kandidat berhasil dimasukkan ke jadwal interview.',
            'wa_panelis' => $waResult,
            'google_calendar' => $calendarResult,
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
                        'created_at' => now($this->timezone),
                        'updated_at' => now($this->timezone),
                    ];
                })->all();

                DB::table('jadwal_interview_kandidat')->insert($rows);
            }
        });

        $waResult = $this->kirimPesanJadwalInterviewKePanelis(
            $jadwalInterviewId,
            $kandidatIds->all()
        );

        $calendarResult = $this->syncGoogleCalendarInterview(
            $jadwalInterviewId,
            $kandidatIds->all()
        );

        return response()->json([
            'success' => true,
            'message' => 'Kandidat jadwal interview berhasil diperbarui.',
            'wa_panelis' => $waResult,
            'google_calendar' => $calendarResult,
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
            'jadwal_interview' => $this->normalizeDateTime($validated['jadwal_interview']),
        ]);

        $kandidatIds = JadwalInterviewKandidat::query()
            ->where('jadwal_interview_id', $jadwalInterviewId)
            ->pluck('data_riwayat_diri_id')
            ->values()
            ->all();

        $waResult = [];
        $calendarResult = [];

        if (!empty($kandidatIds)) {
            $waResult = $this->kirimPesanJadwalInterviewKePanelis(
                $jadwalInterviewId,
                $kandidatIds
            );

            $calendarResult = $this->syncGoogleCalendarInterview(
                $jadwalInterviewId,
                $kandidatIds
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Tanggal interview berhasil diperbarui.',
            'jadwal_interview' => $this->formatDateTimeForJson($jadwal->fresh()->jadwal_interview),
            'wa_panelis' => $waResult,
            'google_calendar' => $calendarResult,
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
        $jadwal = JadwalInterview::query()
            ->whereNull('deleted_at')
            ->find($jadwalInterviewId);

        $calendarResult = $jadwal
            ? $this->deleteGoogleCalendarInterviewEvent($jadwal)
            : [
                'success' => false,
                'message' => 'Jadwal interview tidak ditemukan untuk hapus Google Calendar.',
            ];

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
            'google_calendar' => $calendarResult,
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

        $remainingKandidatIds = JadwalInterviewKandidat::query()
            ->where('jadwal_interview_id', $jadwalInterviewId)
            ->pluck('data_riwayat_diri_id')
            ->values()
            ->all();

        if (!empty($remainingKandidatIds)) {
            $calendarResult = $this->syncGoogleCalendarInterview(
                $jadwalInterviewId,
                $remainingKandidatIds
            );
        } else {
            $jadwal = JadwalInterview::query()
                ->whereNull('deleted_at')
                ->find($jadwalInterviewId);

            $calendarResult = $jadwal
                ? $this->deleteGoogleCalendarInterviewEvent($jadwal)
                : [
                    'success' => false,
                    'message' => 'Jadwal interview tidak ditemukan untuk hapus Google Calendar.',
                ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Kandidat berhasil dihapus dari jadwal interview.',
            'google_calendar' => $calendarResult,
        ]);
    }


    private function jadwalInterviewSelectColumns(): array
    {
        $columns = [
            'id',
            'judul_interview',
            'jadwal_interview',
            'deleted_at',
        ];

        foreach ([
            'google_calendar_event_id',
            'google_calendar_html_link',
            'google_meet_link',
        ] as $column) {
            if (Schema::hasColumn('jadwal_interview', $column)) {
                $columns[] = $column;
            }
        }

        return $columns;
    }

    private function getJadwalInterviewAttribute(?JadwalInterview $jadwal, string $column): ?string
    {
        if (!$jadwal || !Schema::hasColumn('jadwal_interview', $column)) {
            return null;
        }

        $value = $jadwal->getAttribute($column);

        return $value !== null ? (string) $value : null;
    }

    private function updateJadwalInterviewCalendarFields(JadwalInterview $jadwal, array $payload): void
    {
        $allowedPayload = [];

        foreach ($payload as $column => $value) {
            if (Schema::hasColumn('jadwal_interview', $column)) {
                $allowedPayload[$column] = $value;
            }
        }

        if (!empty($allowedPayload)) {
            $jadwal->update($allowedPayload);
        }
    }

    private function syncGoogleCalendarInterview(string $jadwalInterviewId, array $kandidatIds): array
    {
        if (!filter_var(env('GOOGLE_CALENDAR_ENABLED', true), FILTER_VALIDATE_BOOLEAN)) {
            return [
                'success' => false,
                'message' => 'Sinkronisasi Google Calendar dinonaktifkan.',
            ];
        }

        $jadwal = JadwalInterview::query()
            ->whereNull('deleted_at')
            ->find($jadwalInterviewId);

        if (!$jadwal) {
            return [
                'success' => false,
                'message' => 'Jadwal interview tidak ditemukan.',
            ];
        }

        $kandidatIds = collect($kandidatIds)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($kandidatIds)) {
            return [
                'success' => false,
                'message' => 'Tidak ada kandidat untuk dibuatkan Google Calendar.',
            ];
        }

        $kandidats = DB::table('data_riwayat_diri')
            ->leftJoin('posisi', 'posisi.id', '=', 'data_riwayat_diri.posisi_yang_dilamar')
            ->whereIn('data_riwayat_diri.id', $kandidatIds)
            ->select([
                'data_riwayat_diri.id',
                'data_riwayat_diri.nama_lengkap',
                'data_riwayat_diri.nama_panggil',
                'data_riwayat_diri.email',
                'data_riwayat_diri.no_wa',
                'posisi.nama_posisi',
            ])
            ->get();

        $attendees = $kandidats
            ->filter(function ($kandidat) {
                return !empty($kandidat->email)
                    && filter_var($kandidat->email, FILTER_VALIDATE_EMAIL);
            })
            ->map(function ($kandidat) {
                return [
                    'email' => $kandidat->email,
                    'name' => $kandidat->nama_lengkap
                        ?: ($kandidat->nama_panggil ?: $kandidat->email),
                ];
            })
            ->unique('email')
            ->values()
            ->all();

        if (empty($attendees)) {
            return [
                'success' => false,
                'message' => 'Tidak ada email kandidat yang valid untuk dibuatkan Google Calendar.',
                'skipped' => $kandidats
                    ->filter(function ($kandidat) {
                        return empty($kandidat->email)
                            || !filter_var($kandidat->email, FILTER_VALIDATE_EMAIL);
                    })
                    ->map(function ($kandidat) {
                        return [
                            'id' => $kandidat->id,
                            'nama_lengkap' => $kandidat->nama_lengkap,
                            'email' => $kandidat->email,
                            'reason' => 'Email kosong atau tidak valid.',
                        ];
                    })
                    ->values(),
            ];
        }

        $listKandidat = $kandidats
            ->values()
            ->map(function ($kandidat, $index) {
                $no = $index + 1;
                $nama = $kandidat->nama_lengkap ?: ($kandidat->nama_panggil ?: '-');
                $posisi = $kandidat->nama_posisi ?: '-';
                $email = $kandidat->email ?: '-';
                $wa = $kandidat->no_wa ?: '-';

                return "{$no}. {$nama}\n"
                    . "   Posisi: {$posisi}\n"
                    . "   Email: {$email}\n"
                    . "   No. WA: {$wa}";
            })
            ->implode("\n\n");

        try {
            $result = $this->upsertGoogleCalendarEvent($jadwal, $attendees, $listKandidat);

            $this->updateJadwalInterviewCalendarFields($jadwal, [
                'google_calendar_event_id' => $result['event_id'] ?? null,
                'google_calendar_html_link' => $result['html_link'] ?? null,
                'google_meet_link' => $result['meet_link'] ?? null,
            ]);

            return [
                'success' => true,
                'message' => 'Jadwal berhasil dibuat/diperbarui di Google Calendar.',
                'event_id' => $result['event_id'] ?? null,
                'html_link' => $result['html_link'] ?? null,
                'meet_link' => $result['meet_link'] ?? null,
                'total_attendees' => count($attendees),
            ];
        } catch (\Throwable $e) {
            Log::error('Gagal sync Google Calendar interview', [
                'message' => $e->getMessage(),
                'jadwal_interview_id' => $jadwalInterviewId,
                'kandidat_ids' => $kandidatIds,
            ]);

            return [
                'success' => false,
                'message' => 'Gagal membuat jadwal di Google Calendar: ' . $e->getMessage(),
            ];
        }
    }

    private function upsertGoogleCalendarEvent(JadwalInterview $jadwal, array $attendees, string $listKandidat): array
    {
        $calendar = $this->makeGoogleCalendarService();
        $calendarId = env('GOOGLE_CALENDAR_ID', 'primary');
        $eventId = $this->getJadwalInterviewAttribute($jadwal, 'google_calendar_event_id');

        $event = $this->buildGoogleCalendarEvent($jadwal, $attendees, $listKandidat);

        try {
            if ($eventId) {
                $result = $calendar->events->update(
                    $calendarId,
                    $eventId,
                    $event,
                    [
                        'sendUpdates' => 'all',
                        'conferenceDataVersion' => 1,
                    ]
                );
            } else {
                $result = $calendar->events->insert(
                    $calendarId,
                    $event,
                    [
                        'sendUpdates' => 'all',
                        'conferenceDataVersion' => 1,
                    ]
                );
            }
        } catch (GoogleServiceException $e) {
            if ((int) $e->getCode() !== 404 || !$eventId) {
                throw $e;
            }

            $result = $calendar->events->insert(
                $calendarId,
                $event,
                [
                    'sendUpdates' => 'all',
                    'conferenceDataVersion' => 1,
                ]
            );
        }

        return [
            'event_id' => $result->getId(),
            'html_link' => $result->getHtmlLink(),
            'meet_link' => $result->getHangoutLink(),
        ];
    }

    private function buildGoogleCalendarEvent(JadwalInterview $jadwal, array $attendees, string $listKandidat): GoogleCalendarEvent
    {
        $timezone = env('GOOGLE_CALENDAR_TIMEZONE', $this->timezone);
        $durationMinutes = (int) env('GOOGLE_CALENDAR_EVENT_DURATION_MINUTES', 60);

        if ($durationMinutes <= 0) {
            $durationMinutes = 60;
        }

        $start = Carbon::parse($jadwal->jadwal_interview, $timezone);
        $end = (clone $start)->addMinutes($durationMinutes);

        $googleAttendees = collect($attendees)
            ->map(function ($attendee) {
                return new EventAttendee([
                    'email' => $attendee['email'],
                    'displayName' => $attendee['name'] ?? $attendee['email'],
                    'optional' => false,
                ]);
            })
            ->values()
            ->all();

        $description = "Jadwal interview kandidat.\n\n"
            . "Judul Interview: " . ($jadwal->judul_interview ?: '-') . "\n"
            . "Tanggal Interview: " . $this->formatDateTimeForDisplay($jadwal->jadwal_interview) . "\n\n"
            . "Daftar Kandidat:\n"
            . $listKandidat;

        $event = new GoogleCalendarEvent([
            'summary' => $jadwal->judul_interview ?: 'Jadwal Interview Kandidat',
            'description' => $description,
            'location' => env('GOOGLE_CALENDAR_DEFAULT_LOCATION', 'Google Meet'),
            'start' => new EventDateTime([
                'dateTime' => $start->toRfc3339String(),
                'timeZone' => $timezone,
            ]),
            'end' => new EventDateTime([
                'dateTime' => $end->toRfc3339String(),
                'timeZone' => $timezone,
            ]),
            'attendees' => $googleAttendees,
        ]);

        if (filter_var(env('GOOGLE_CALENDAR_CREATE_MEET', true), FILTER_VALIDATE_BOOLEAN)) {
            $conferenceSolutionKey = new ConferenceSolutionKey();
            $conferenceSolutionKey->setType('hangoutsMeet');

            $createConferenceRequest = new CreateConferenceRequest();
            $createConferenceRequest->setRequestId('interview-' . $jadwal->id . '-' . Str::random(8));
            $createConferenceRequest->setConferenceSolutionKey($conferenceSolutionKey);

            $conferenceData = new ConferenceData();
            $conferenceData->setCreateRequest($createConferenceRequest);

            $event->setConferenceData($conferenceData);
        }

        return $event;
    }

    private function makeGoogleCalendarService(): Calendar
    {
        if (!class_exists(GoogleClient::class)) {
            throw new \RuntimeException('Package google/apiclient belum terinstall. Jalankan: composer require google/apiclient:^2.0');
        }

        $credentialsConfig = env('GOOGLE_CALENDAR_CREDENTIALS');

        if (empty($credentialsConfig)) {
            throw new \RuntimeException('GOOGLE_CALENDAR_CREDENTIALS belum diatur di file .env.');
        }

        $credentialsPath = $credentialsConfig;

        if (!Str::startsWith($credentialsPath, ['/'])) {
            $credentialsPath = base_path($credentialsPath);
        }

        if (!file_exists($credentialsPath)) {
            throw new \RuntimeException('File credential Google Calendar tidak ditemukan: ' . $credentialsPath);
        }

        $client = new GoogleClient();
        $client->setApplicationName(env('APP_NAME', 'Recruitment') . ' Google Calendar');
        $client->setScopes([
            Calendar::CALENDAR,
        ]);
        $client->setAuthConfig($credentialsPath);

        $impersonateEmail = env('GOOGLE_CALENDAR_IMPERSONATE_EMAIL');

        if (!empty($impersonateEmail)) {
            $client->setSubject($impersonateEmail);
        }

        return new Calendar($client);
    }

    private function deleteGoogleCalendarInterviewEvent(JadwalInterview $jadwal): array
    {
        $eventId = $this->getJadwalInterviewAttribute($jadwal, 'google_calendar_event_id');

        if (!$eventId) {
            return [
                'success' => true,
                'message' => 'Tidak ada event Google Calendar yang perlu dihapus.',
            ];
        }

        try {
            $calendar = $this->makeGoogleCalendarService();
            $calendar->events->delete(
                env('GOOGLE_CALENDAR_ID', 'primary'),
                $eventId,
                [
                    'sendUpdates' => 'all',
                ]
            );

            $this->updateJadwalInterviewCalendarFields($jadwal, [
                'google_calendar_event_id' => null,
                'google_calendar_html_link' => null,
                'google_meet_link' => null,
            ]);

            return [
                'success' => true,
                'message' => 'Event Google Calendar berhasil dihapus.',
            ];
        } catch (GoogleServiceException $e) {
            if ((int) $e->getCode() === 404) {
                $this->updateJadwalInterviewCalendarFields($jadwal, [
                    'google_calendar_event_id' => null,
                    'google_calendar_html_link' => null,
                    'google_meet_link' => null,
                ]);

                return [
                    'success' => true,
                    'message' => 'Event Google Calendar sudah tidak ditemukan, data lokal dibersihkan.',
                ];
            }

            Log::error('Gagal hapus Google Calendar interview', [
                'message' => $e->getMessage(),
                'jadwal_interview_id' => $jadwal->id,
                'event_id' => $eventId,
            ]);

            return [
                'success' => false,
                'message' => 'Gagal menghapus event Google Calendar: ' . $e->getMessage(),
            ];
        } catch (\Throwable $e) {
            Log::error('Gagal hapus Google Calendar interview', [
                'message' => $e->getMessage(),
                'jadwal_interview_id' => $jadwal->id,
                'event_id' => $eventId,
            ]);

            return [
                'success' => false,
                'message' => 'Gagal menghapus event Google Calendar: ' . $e->getMessage(),
            ];
        }
    }


    private function kirimPesanJadwalInterviewKePanelis(string $jadwalInterviewId, array $kandidatIds): array
    {
        $jadwal = JadwalInterview::query()
            ->whereNull('deleted_at')
            ->find($jadwalInterviewId);

        if (!$jadwal) {
            return [
                'success' => false,
                'message' => 'Jadwal interview tidak ditemukan.',
                'total_dikirim' => 0,
                'total_dilewati' => 0,
                'total_gagal_provider' => 0,
                'skipped' => [],
                'responses' => [],
            ];
        }

        $panelis = JadwalInterviewPanelis::query()
            ->with('interviewer:id,nama,no_wa,deleted_at')
            ->where('jadwal_interview_id', $jadwalInterviewId)
            ->get()
            ->map(function ($item) {
                return $item->interviewer;
            })
            ->filter()
            ->values();

        if ($panelis->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Panelis/interviewer belum diatur untuk jadwal interview ini.',
                'total_dikirim' => 0,
                'total_dilewati' => 1,
                'total_gagal_provider' => 0,
                'skipped' => [
                    [
                        'jadwal_interview_id' => $jadwalInterviewId,
                        'reason' => 'Tidak ada data panelis di tabel jadwal_interview_panelis.',
                    ],
                ],
                'responses' => [],
            ];
        }

        $kandidats = DB::table('data_riwayat_diri')
            ->leftJoin('posisi', 'posisi.id', '=', 'data_riwayat_diri.posisi_yang_dilamar')
            ->leftJoin('data_perusahaan', 'data_perusahaan.id', '=', 'data_riwayat_diri.perusahaan_dilamar')
            ->whereIn('data_riwayat_diri.id', $kandidatIds)
            ->select([
                'data_riwayat_diri.id',
                'data_riwayat_diri.nama_lengkap',
                'data_riwayat_diri.nama_panggil',
                'data_riwayat_diri.no_wa',
                'data_riwayat_diri.perusahaan_dilamar',
                'data_riwayat_diri.posisi_yang_dilamar',
                'posisi.nama_posisi',
                'data_perusahaan.id as perusahaan_id',
                'data_perusahaan.nama_perusahaan',
                'data_perusahaan.no_wa as no_wa_perusahaan',
                'data_perusahaan.token_api_wa',
            ])
            ->get();

        if ($kandidats->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Data kandidat tidak ditemukan.',
                'total_dikirim' => 0,
                'total_dilewati' => 0,
                'total_gagal_provider' => 0,
                'skipped' => [],
                'responses' => [],
            ];
        }

        $groupedMessages = [];
        $skipped = [];

        foreach ($kandidats as $kandidat) {
            if (empty($kandidat->perusahaan_id)) {
                $skipped[] = [
                    'kandidat_id' => $kandidat->id,
                    'nama_lengkap' => $kandidat->nama_lengkap,
                    'reason' => 'Data perusahaan kandidat tidak ditemukan.',
                ];

                continue;
            }

            $tokenApiWa = $this->normalizeTokenApiWa($kandidat->token_api_wa ?? null);
            $nomorPerusahaan = $this->normalizeWhatsappNumber($kandidat->no_wa_perusahaan ?? null);

            if (!$nomorPerusahaan) {
                $skipped[] = [
                    'kandidat_id' => $kandidat->id,
                    'nama_lengkap' => $kandidat->nama_lengkap,
                    'perusahaan_id' => $kandidat->perusahaan_id,
                    'perusahaan' => $kandidat->nama_perusahaan,
                    'reason' => 'Nomor WhatsApp perusahaan kosong atau tidak valid.',
                ];

                continue;
            }

            if (!$tokenApiWa) {
                $skipped[] = [
                    'kandidat_id' => $kandidat->id,
                    'nama_lengkap' => $kandidat->nama_lengkap,
                    'perusahaan_id' => $kandidat->perusahaan_id,
                    'perusahaan' => $kandidat->nama_perusahaan,
                    'nomer_perusahaan' => $nomorPerusahaan,
                    'reason' => 'Token API WA perusahaan kosong.',
                ];

                continue;
            }

            $groupKey = (string) $kandidat->perusahaan_id;

            if (!isset($groupedMessages[$groupKey])) {
                $groupedMessages[$groupKey] = [
                    'perusahaan_id' => $kandidat->perusahaan_id,
                    'perusahaan' => $kandidat->nama_perusahaan,
                    'nomer_perusahaan' => $nomorPerusahaan,
                    'token_api_wa' => $tokenApiWa,
                    'kandidats' => [],
                    'messages' => [],
                ];
            }

            $groupedMessages[$groupKey]['kandidats'][] = [
                'id' => $kandidat->id,
                'nama' => $kandidat->nama_lengkap ?: ($kandidat->nama_panggil ?: '-'),
                'nama_panggil' => $kandidat->nama_panggil ?: '-',
                'no_wa' => $kandidat->no_wa ?: '-',
                'posisi' => $kandidat->nama_posisi ?: '-',
            ];
        }

        if (empty($groupedMessages)) {
            return [
                'success' => false,
                'message' => 'Tidak ada data valid untuk dikirim. Pastikan kandidat punya perusahaan, perusahaan punya no_wa, dan token_api_wa.',
                'total_dikirim' => 0,
                'total_dilewati' => count($skipped),
                'total_gagal_provider' => 0,
                'skipped' => $skipped,
                'responses' => [],
            ];
        }

        foreach ($groupedMessages as $groupKey => $group) {
            foreach ($panelis as $interviewer) {
                $target = $this->normalizeWhatsappNumber($interviewer->no_wa ?? null);

                if (!$target) {
                    $skipped[] = [
                        'interviewer_id' => $interviewer->id ?? null,
                        'interviewer' => $interviewer->nama ?? null,
                        'no_wa' => $interviewer->no_wa ?? null,
                        'perusahaan_id' => $group['perusahaan_id'],
                        'perusahaan' => $group['perusahaan'],
                        'reason' => 'Nomor WhatsApp interviewer kosong atau tidak valid.',
                    ];

                    continue;
                }

                $groupedMessages[$groupKey]['messages'][] = [
                    'target' => $target,
                    'message' => $this->buildPesanJadwalInterviewUntukPanelis($jadwal, $group, $interviewer),
                    'delay' => '2',
                ];
            }
        }

        $groupedMessages = collect($groupedMessages)
            ->filter(function ($group) {
                return !empty($group['messages']);
            })
            ->values()
            ->all();

        if (empty($groupedMessages)) {
            return [
                'success' => false,
                'message' => 'Tidak ada nomor interviewer valid untuk dikirim pesan.',
                'total_dikirim' => 0,
                'total_dilewati' => count($skipped),
                'total_gagal_provider' => 0,
                'skipped' => $skipped,
                'responses' => [],
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

                $targetGroup = collect($group['messages'])->pluck('target')->values()->all();
                $targets = array_merge($targets, $targetGroup);

                $responses[] = [
                    'success' => $isSuccess,
                    'perusahaan_id' => $group['perusahaan_id'],
                    'perusahaan' => $group['perusahaan'],
                    'nomer_perusahaan' => $group['nomer_perusahaan'],
                    'total_kandidat' => count($group['kandidats']),
                    'total_pesan' => $countMessages,
                    'targets' => $targetGroup,
                    'fonnte_http_code' => $response->status(),
                    'fonnte_response' => $json ?: $response->body(),
                    'message' => $isSuccess
                        ? 'Pesan jadwal interview berhasil dikirim ke panelis untuk perusahaan ini.'
                        : ($json['reason'] ?? $json['detail'] ?? $json['message'] ?? 'Pesan gagal dikirim melalui Fonnte.'),
                ];
            } catch (\Throwable $e) {
                $countMessages = count($group['messages']);
                $totalGagalProvider += $countMessages;

                Log::error('Gagal mengirim WA jadwal interview ke panelis', [
                    'message' => $e->getMessage(),
                    'jadwal_interview_id' => $jadwal->id ?? null,
                    'perusahaan_id' => $group['perusahaan_id'],
                    'perusahaan' => $group['perusahaan'],
                ]);

                $responses[] = [
                    'success' => false,
                    'perusahaan_id' => $group['perusahaan_id'],
                    'perusahaan' => $group['perusahaan'],
                    'nomer_perusahaan' => $group['nomer_perusahaan'],
                    'total_kandidat' => count($group['kandidats']),
                    'total_pesan' => $countMessages,
                    'targets' => collect($group['messages'])->pluck('target')->values()->all(),
                    'message' => 'Terjadi kesalahan saat mengirim pesan Fonnte: ' . $e->getMessage(),
                ];
            }
        }

        $isAllSuccess = $totalDikirim > 0 && $totalGagalProvider === 0;
        $isPartialSuccess = $totalDikirim > 0 && $totalGagalProvider > 0;

        return [
            'success' => $totalDikirim > 0,
            'message' => $isAllSuccess
                ? 'Pesan jadwal interview berhasil dikirim ke panelis sesuai perusahaan.'
                : ($isPartialSuccess
                    ? 'Sebagian pesan jadwal interview berhasil dikirim, sebagian gagal.'
                    : 'Pesan jadwal interview gagal dikirim.'),
            'total_dikirim' => $totalDikirim,
            'total_dilewati' => count($skipped),
            'total_gagal_provider' => $totalGagalProvider,
            'total_perusahaan' => count($groupedMessages),
            'skipped' => $skipped,
            'targets' => array_values(array_unique($targets)),
            'responses' => $responses,
        ];
    }

    private function buildPesanJadwalInterviewUntukPanelis(JadwalInterview $jadwal, array $group, $interviewer): string
    {
        $tanggalInterview = $this->formatDateTimeForDisplay($jadwal->jadwal_interview);

        $namaHeadDivisi = $interviewer->nama ?? 'Bapak/Ibu Head Divisi';

        $listKandidat = collect($group['kandidats'])
            ->values()
            ->map(function ($kandidat, $index) {
                $no = $index + 1;

                return "{$no}. {$kandidat['nama']}\n"
                    . "   Posisi yang Dilamar: {$kandidat['posisi']}\n"
                    . "   No. WhatsApp: {$kandidat['no_wa']}";
            })
            ->implode("\n\n");

        return "Yth. {$namaHeadDivisi},\n\n"
            . "Dengan hormat,\n\n"
            . "Kami informasikan bahwa terdapat jadwal interview kandidat untuk kebutuhan rekrutmen di {$group['perusahaan']}.\n\n"
            . "Berikut detail jadwal interview:\n"
            . "Judul Interview: " . ($jadwal->judul_interview ?: '-') . "\n"
            . "Tanggal Interview: {$tanggalInterview}\n\n"
            . "Daftar kandidat yang dijadwalkan:\n"
            . "{$listKandidat}\n\n"
            . "Mohon kesediaan Bapak/Ibu untuk melakukan proses interview sesuai jadwal yang telah ditentukan.\n"
            . "Apabila terdapat kendala atau perlu dilakukan penyesuaian jadwal, mohon segera menginformasikan kepada tim rekrutmen.\n\n"
            . "Terima kasih atas perhatian dan kerja samanya.\n\n"
            . "Hormat kami,\n"
            . "Tim Rekrutmen {$group['perusahaan']}";
    }

    private function normalizeWhatsappNumber(?string $number): ?string
    {
        if (!$number) {
            return null;
        }

        $number = preg_replace('/[^0-9]/', '', $number);

        if (!$number) {
            return null;
        }

        if (str_starts_with($number, '0')) {
            $number = '62' . substr($number, 1);
        }

        if (str_starts_with($number, '8')) {
            $number = '62' . $number;
        }

        if (!str_starts_with($number, '62')) {
            return null;
        }

        return $number;
    }

    private function normalizeTokenApiWa(?string $token): ?string
    {
        $token = trim((string) $token);

        return $token !== '' ? $token : null;
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

        $tanggalInterview = Carbon::parse($jadwal->jadwal_interview, $this->timezone);
        $sekarang = now($this->timezone);

        if ($tanggalInterview->lt($sekarang)) {
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