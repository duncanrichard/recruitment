<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HasilReviewManagement;
use App\Models\JadwalOfferingLetter;
use App\Services\RecruitmentWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class JadwalOfferingLetterController extends Controller
{
    private string $timezone = 'Asia/Jakarta';

    public function list()
    {
        $query = JadwalOfferingLetter::with([
            'hasilReviewManagement.hasilInterview.kandidat.perusahaan',
        ])
            ->latest();

        $this->applyOfferingLetterCompanyScope($query);

        $data = $query
            ->get()
            ->map(function ($item) {
                $hasilReview = $item->hasilReviewManagement;
                $kandidat = $this->getKandidatData($hasilReview);

                return [
                    'id' => $item->id,
                    'hasil_review_management_id' => $item->hasil_review_management_id,
                    'tanggal_ol' => $item->tanggal_ol,
                    'jam_ol' => $item->jam_ol,
                    'metode' => $item->metode,
                    'link' => $item->link,
                    'pic' => $item->pic,
                    'catatan' => $item->catatan,
                    'status_jadwal' => $item->status_jadwal ?: 'Pending',
                    'hasil_review_management' => $hasilReview,
                    'nama_kandidat' => $this->getNamaKandidat($hasilReview),
                    'kandidat_label' => $this->makeKandidatLabel($hasilReview),
                    'perusahaan_id' => $kandidat?->perusahaan_id,
                    'perusahaan_kode' => $kandidat?->perusahaan_kode,
                    'perusahaan_nama' => $kandidat?->nama_perusahaan,
                    'perusahaan_label' => $kandidat?->nama_perusahaan ?: '-',
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function candidates()
    {
        $query = HasilReviewManagement::with([
            'hasilInterview.kandidat.perusahaan',
        ])
            ->whereRaw('LOWER(status) = ?', ['diterima'])
            ->whereDoesntHave('jadwalOfferingLetter')
            ->latest();

        $this->applyReviewManagementCompanyScope($query);

        $data = $query
            ->get()
            ->map(function ($item) {
                $kandidat = $this->getKandidatData($item);

                return [
                    'id' => $item->id,
                    'hasil_interview_id' => $item->hasil_interview_id,
                    'review_management' => $item->review_management,
                    'status' => $item->status,
                    'nama_kandidat' => $this->getNamaKandidat($item),
                    'label' => $this->makeKandidatLabel($item),
                    'perusahaan_id' => $kandidat?->perusahaan_id,
                    'perusahaan_kode' => $kandidat?->perusahaan_kode,
                    'perusahaan_nama' => $kandidat?->nama_perusahaan,
                    'perusahaan_label' => $kandidat?->nama_perusahaan ?: '-',
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'hasil_review_management_id' => [
                'required',
                'uuid',
                'exists:hasil_review_management,id',
                'unique:jadwal_offering_letters,hasil_review_management_id',
            ],
            'tanggal_ol' => ['required', 'date'],
            'jam_ol' => ['required'],
            'metode' => ['required', 'string', 'max:50'],
            'link' => ['nullable', 'string', 'max:255'],
            'pic' => ['nullable', 'string', 'max:255'],
            'catatan' => ['nullable', 'string'],
        ]);

        $review = HasilReviewManagement::where('id', $validated['hasil_review_management_id'])
            ->whereRaw('LOWER(status) = ?', ['diterima'])
            ->first();

        if (! $review) {
            return response()->json([
                'success' => false,
                'message' => 'Kandidat belum berstatus Diterima.',
            ], 422);
        }

        app(RecruitmentWorkflowService::class)->assertTransition('accepted', 'offering_scheduled');

        if (! $this->reviewManagementIsAllowedForCurrentUser($review)) {
            return response()->json([
                'success' => false,
                'message' => 'Kandidat tidak sesuai dengan perusahaan account yang login.',
            ], 403);
        }

        $validated['status_jadwal'] = null;

        $data = JadwalOfferingLetter::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal Offering Letter berhasil dibuat.',
            'data' => $data,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $data = $this->findOfferingLetterForCurrentUserOrFail($id);

        $validated = $request->validate([
            'hasil_review_management_id' => [
                'required',
                'uuid',
                'exists:hasil_review_management,id',
                'unique:jadwal_offering_letters,hasil_review_management_id,'.$data->id,
            ],
            'tanggal_ol' => ['required', 'date'],
            'jam_ol' => ['required'],
            'metode' => ['required', 'string', 'max:50'],
            'link' => ['nullable', 'string', 'max:255'],
            'pic' => ['nullable', 'string', 'max:255'],
            'catatan' => ['nullable', 'string'],
        ]);

        $review = HasilReviewManagement::where('id', $validated['hasil_review_management_id'])
            ->whereRaw('LOWER(status) = ?', ['diterima'])
            ->first();

        if (! $review) {
            return response()->json([
                'success' => false,
                'message' => 'Kandidat belum berstatus Diterima.',
            ], 422);
        }

        app(RecruitmentWorkflowService::class)->assertTransition('accepted', 'offering_scheduled');

        if (! $this->reviewManagementIsAllowedForCurrentUser($review)) {
            return response()->json([
                'success' => false,
                'message' => 'Kandidat tidak sesuai dengan perusahaan account yang login.',
            ], 403);
        }

        $data->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal Offering Letter berhasil diperbarui.',
            'data' => $data,
        ]);
    }

    public function updateStatus(Request $request, string $id)
    {
        $data = $this->findOfferingLetterForCurrentUserOrFail($id);

        $validated = $request->validate([
            'status_jadwal' => [
                'nullable',
                'string',
                Rule::in([
                    'Menerima',
                    'Menolak',
                    'Tidak Melanjutkan',
                ]),
            ],
        ]);

        $status = $validated['status_jadwal'] ?: null;

        $data->update([
            'status_jadwal' => $status,
        ]);

        $message = match ($status) {
            'Menerima' => 'Selamat, kandidat menerima Offering Letter. Semoga ini menjadi awal perjalanan karier yang sukses dan penuh semangat.',
            'Menolak' => 'Kandidat menolak Offering Letter. Terima kasih atas konfirmasinya, semoga sukses untuk perjalanan karier berikutnya.',
            'Tidak Melanjutkan' => 'Kandidat tidak melanjutkan proses Offering Letter. Tetap semangat, semoga ada kesempatan terbaik di waktu berikutnya.',
            default => 'Status Offering Letter berhasil dikosongkan dan kembali menjadi Pending.',
        };

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ]);
    }

    public function destroy(string $id)
    {
        $data = $this->findOfferingLetterForCurrentUserOrFail($id);

        $data->delete();

        return response()->json([
            'success' => true,
            'message' => 'Jadwal Offering Letter berhasil dihapus.',
        ]);
    }

    /**
     * Ambil identitas kandidat dan perusahaan untuk tampilan serta company scope.
     * Data kredensial WhatsApp sengaja tidak ikut dipilih.
     */
    private function getKandidatData(?HasilReviewManagement $review): ?object
    {
        $kandidatId = $this->getKandidatIdFromReview($review);

        if (! $kandidatId || ! Schema::hasTable('data_riwayat_diri')) {
            return null;
        }

        $query = DB::table('data_riwayat_diri as drd')
            ->leftJoin('data_perusahaan as dp', 'dp.id', '=', 'drd.perusahaan_dilamar')
            ->where('drd.id', $kandidatId);

        if (Schema::hasColumn('data_riwayat_diri', 'deleted_at')) {
            $query->whereNull('drd.deleted_at');
        }

        return $query
            ->select([
                'drd.id',
                'drd.nama_lengkap',
                'drd.perusahaan_dilamar',
                'dp.id as perusahaan_id',
                'dp.kode as perusahaan_kode',
                'dp.nama_perusahaan',
            ])
            ->first();
    }

    private function getKandidatIdFromReview(?HasilReviewManagement $review): ?string
    {
        if (! $review) {
            return null;
        }

        $fromRelation = $review->hasilInterview?->kandidat?->id ?? null;

        if ($fromRelation) {
            return (string) $fromRelation;
        }

        if (! empty($review->hasil_interview_id)) {
            $id = DB::table('jadwal_interview_kandidat')
                ->where('id', $review->hasil_interview_id)
                ->value('data_riwayat_diri_id');

            if ($id) {
                return (string) $id;
            }
        }

        if (
            Schema::hasColumn('hasil_review_management', 'daftar_hadir_test_zoom_id') &&
            ! empty($review->daftar_hadir_test_zoom_id)
        ) {
            $id = DB::table('daftar_hadir_test_zoom')
                ->where('id', $review->daftar_hadir_test_zoom_id)
                ->value('data_riwayat_diri_id');

            if ($id) {
                return (string) $id;
            }
        }

        if (
            Schema::hasColumn('hasil_review_management', 'daftar_hadir_test_mmpi_id') &&
            ! empty($review->daftar_hadir_test_mmpi_id)
        ) {
            $id = DB::table('daftar_hadir_test_mmpi')
                ->where('id', $review->daftar_hadir_test_mmpi_id)
                ->value('data_riwayat_diri_id');

            if ($id) {
                return (string) $id;
            }
        }

        return null;
    }

    private function getNamaKandidat(?HasilReviewManagement $item): string
    {
        if (! $item) {
            return '-';
        }

        $namaFromRelation = $item->hasilInterview?->kandidat?->nama_lengkap;

        if ($namaFromRelation) {
            return $namaFromRelation;
        }

        $kandidatId = $this->getKandidatIdFromReview($item);

        if (! $kandidatId) {
            return '-';
        }

        return DB::table('data_riwayat_diri')
            ->where('id', $kandidatId)
            ->value('nama_lengkap') ?: '-';
    }

    private function makeKandidatLabel(?HasilReviewManagement $item): string
    {
        if (! $item) {
            return '-';
        }

        $nama = $this->getNamaKandidat($item);
        $source = $this->getReviewSourceLabel($item);

        return $source !== '-'
            ? "{$nama} - {$source}"
            : $nama;
    }

    private function getReviewSourceLabel(HasilReviewManagement $review): string
    {
        $sumberReview = Schema::hasColumn('hasil_review_management', 'sumber_review')
            ? strtolower((string) ($review->sumber_review ?? ''))
            : '';

        if ($sumberReview === 'test_zoom') {
            return 'Hasil Test Zoom';
        }

        if ($sumberReview === 'test_mmpi') {
            return 'Hasil Test MMPI';
        }

        if (! empty($review->hasil_interview_id)) {
            return 'Interview';
        }

        if (Schema::hasColumn('hasil_review_management', 'daftar_hadir_test_zoom_id') && ! empty($review->daftar_hadir_test_zoom_id)) {
            return 'Hasil Test Zoom';
        }

        if (Schema::hasColumn('hasil_review_management', 'daftar_hadir_test_mmpi_id') && ! empty($review->daftar_hadir_test_mmpi_id)) {
            return 'Hasil Test MMPI';
        }

        return '-';
    }

    private function formatTanggalIndonesia($value): string
    {
        if (! $value) {
            return '-';
        }

        try {
            return Carbon::parse($value, $this->timezone)
                ->locale('id')
                ->translatedFormat('d F Y');
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    private function formatJam($value): string
    {
        if (! $value) {
            return '-';
        }

        try {
            return Carbon::parse($value)->format('H:i');
        } catch (\Throwable) {
            return substr((string) $value, 0, 5) ?: '-';
        }
    }

    private function findOfferingLetterForCurrentUserOrFail(string $id): JadwalOfferingLetter
    {
        $query = JadwalOfferingLetter::query();

        $this->applyOfferingLetterCompanyScope($query);

        return $query->findOrFail($id);
    }

    private function applyOfferingLetterCompanyScope($query): void
    {
        $allowedPerusahaanIds = $this->currentUserPerusahaanIds();

        if (! is_array($allowedPerusahaanIds)) {
            return;
        }

        if (empty($allowedPerusahaanIds)) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereIn('hasil_review_management_id', function ($subQuery) use ($allowedPerusahaanIds) {
            $this->selectScopedReviewManagementIds($subQuery, $allowedPerusahaanIds);
        });
    }

    private function applyReviewManagementCompanyScope($query): void
    {
        $allowedPerusahaanIds = $this->currentUserPerusahaanIds();

        if (! is_array($allowedPerusahaanIds)) {
            return;
        }

        if (empty($allowedPerusahaanIds)) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereIn('id', function ($subQuery) use ($allowedPerusahaanIds) {
            $this->selectScopedReviewManagementIds($subQuery, $allowedPerusahaanIds);
        });
    }

    private function selectScopedReviewManagementIds($query, array $allowedPerusahaanIds): void
    {
        $query
            ->select('hrm.id')
            ->from('hasil_review_management as hrm')
            ->leftJoin('jadwal_interview_kandidat as jik', 'jik.id', '=', 'hrm.hasil_interview_id')
            ->leftJoin('data_riwayat_diri as drd_interview', 'drd_interview.id', '=', 'jik.data_riwayat_diri_id');

        if (
            Schema::hasColumn('hasil_review_management', 'daftar_hadir_test_zoom_id') &&
            Schema::hasTable('daftar_hadir_test_zoom')
        ) {
            $query
                ->leftJoin('daftar_hadir_test_zoom as dhz', 'dhz.id', '=', 'hrm.daftar_hadir_test_zoom_id')
                ->leftJoin('data_riwayat_diri as drd_zoom', 'drd_zoom.id', '=', 'dhz.data_riwayat_diri_id');
        }

        if (
            Schema::hasColumn('hasil_review_management', 'daftar_hadir_test_mmpi_id') &&
            Schema::hasTable('daftar_hadir_test_mmpi')
        ) {
            $query
                ->leftJoin('daftar_hadir_test_mmpi as dhm', 'dhm.id', '=', 'hrm.daftar_hadir_test_mmpi_id')
                ->leftJoin('data_riwayat_diri as drd_mmpi', 'drd_mmpi.id', '=', 'dhm.data_riwayat_diri_id');
        }

        $query->where(function ($where) use ($allowedPerusahaanIds) {
            $where->whereIn('drd_interview.perusahaan_dilamar', $allowedPerusahaanIds);

            if (
                Schema::hasColumn('hasil_review_management', 'daftar_hadir_test_zoom_id') &&
                Schema::hasTable('daftar_hadir_test_zoom')
            ) {
                $where->orWhereIn('drd_zoom.perusahaan_dilamar', $allowedPerusahaanIds);
            }

            if (
                Schema::hasColumn('hasil_review_management', 'daftar_hadir_test_mmpi_id') &&
                Schema::hasTable('daftar_hadir_test_mmpi')
            ) {
                $where->orWhereIn('drd_mmpi.perusahaan_dilamar', $allowedPerusahaanIds);
            }
        });
    }

    private function reviewManagementIsAllowedForCurrentUser(HasilReviewManagement $review): bool
    {
        $allowedPerusahaanIds = $this->currentUserPerusahaanIds();

        if (! is_array($allowedPerusahaanIds)) {
            return true;
        }

        if (empty($allowedPerusahaanIds)) {
            return false;
        }

        $kandidat = $this->getKandidatData($review);

        if (! $kandidat || empty($kandidat->perusahaan_id)) {
            return false;
        }

        return in_array((string) $kandidat->perusahaan_id, array_map('strval', $allowedPerusahaanIds), true);
    }

    /**
     * Return:
     * - null  => user boleh akses semua perusahaan, contoh Superadmin.
     * - array => user hanya boleh akses perusahaan tertentu.
     */
    private function currentUserPerusahaanIds(): ?array
    {
        $user = Auth::user();

        if (! $user) {
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
                    ->map(fn ($id) => (string) $id)
                    ->values()
                    ->all();
            }
        } catch (\Throwable $e) {
            $ids = [];
        }

        if (empty($ids) && ! empty($user->perusahaan_id)) {
            $ids[] = (string) $user->perusahaan_id;
        }

        if (empty($ids) && ! empty($user->data_perusahaan_id)) {
            $ids[] = (string) $user->data_perusahaan_id;
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
            if (method_exists($user, 'hasRole') && $user->hasRole(['superadmin', 'Superadmin', 'super admin', 'Super Admin'])) {
                return true;
            }
        } catch (\Throwable $e) {
            //
        }

        try {
            if (method_exists($user, 'roles')) {
                $roleNames = $user->roles()
                    ->pluck('name')
                    ->map(fn ($name) => strtolower(trim((string) $name)))
                    ->values()
                    ->all();

                return collect($roleNames)->contains(function ($name) {
                    return in_array($name, [
                        'superadmin',
                        'super admin',
                    ], true);
                });
            }
        } catch (\Throwable $e) {
            //
        }

        $roleValue = strtolower(trim((string) ($user->role ?? $user->role_name ?? '')));

        return in_array($roleValue, [
            'superadmin',
            'super admin',
        ], true);
    }
}
