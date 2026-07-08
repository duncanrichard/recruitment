<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HasilReviewManagement;
use App\Models\JadwalOfferingLetter;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
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
            $kandidat = $this->getDataKandidatUntukWa($hasilReview);

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
            $kandidat = $this->getDataKandidatUntukWa($item);

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

    if (!$review) {
        return response()->json([
            'success' => false,
            'message' => 'Kandidat belum berstatus Diterima.',
        ], 422);
    }

    if (!$this->reviewManagementIsAllowedForCurrentUser($review)) {
        return response()->json([
            'success' => false,
            'message' => 'Kandidat tidak sesuai dengan perusahaan account yang login.',
        ], 403);
    }

    $validated['status_jadwal'] = null;

    $data = JadwalOfferingLetter::create($validated);

    $waResult = $this->kirimPesanJadwalOlKeKandidat($data->fresh());

    return response()->json([
        'success' => true,
        'message' => ($waResult['success'] ?? false)
            ? 'Jadwal Offering Letter berhasil dibuat dan pesan WhatsApp berhasil dikirim ke kandidat.'
            : 'Jadwal Offering Letter berhasil dibuat, tetapi pesan WhatsApp belum berhasil dikirim ke kandidat.',
        'data' => $data,
        'wa_kandidat' => $waResult,
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
            'unique:jadwal_offering_letters,hasil_review_management_id,' . $data->id,
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

    if (!$review) {
        return response()->json([
            'success' => false,
            'message' => 'Kandidat belum berstatus Diterima.',
        ], 422);
    }

    if (!$this->reviewManagementIsAllowedForCurrentUser($review)) {
        return response()->json([
            'success' => false,
            'message' => 'Kandidat tidak sesuai dengan perusahaan account yang login.',
        ], 403);
    }

    $data->update($validated);

    $waResult = $this->kirimPesanJadwalOlKeKandidat($data->fresh(), true);

    return response()->json([
        'success' => true,
        'message' => ($waResult['success'] ?? false)
            ? 'Jadwal Offering Letter berhasil diperbarui dan pesan WhatsApp berhasil dikirim ke kandidat.'
            : 'Jadwal Offering Letter berhasil diperbarui, tetapi pesan WhatsApp belum berhasil dikirim ke kandidat.',
        'data' => $data,
        'wa_kandidat' => $waResult,
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


    private function kirimPesanJadwalOlKeKandidat(JadwalOfferingLetter $jadwalOl, bool $isUpdate = false): array
    {
        $jadwalOl->loadMissing('hasilReviewManagement.hasilInterview.kandidat');

        $review = $jadwalOl->hasilReviewManagement;

        if (!$review) {
            return $this->waFailed('Data review management tidak ditemukan.', [
                'jadwal_ol_id' => $jadwalOl->id ?? null,
            ]);
        }

        $kandidat = $this->getDataKandidatUntukWa($review);

        if (!$kandidat) {
            return $this->waFailed('Data kandidat tidak ditemukan dari review management.', [
                'jadwal_ol_id' => $jadwalOl->id ?? null,
                'hasil_review_management_id' => $review->id ?? null,
            ]);
        }

        $target = $this->normalizeWhatsappNumber($kandidat->no_wa ?? null);

        if (!$target) {
            return $this->waFailed('Nomor WhatsApp kandidat kosong atau tidak valid.', [
                'jadwal_ol_id' => $jadwalOl->id ?? null,
                'kandidat_id' => $kandidat->id ?? null,
                'nama_kandidat' => $kandidat->nama_lengkap ?? null,
                'no_wa' => $kandidat->no_wa ?? null,
            ]);
        }

        $nomorPerusahaan = $this->normalizeWhatsappNumber($kandidat->no_wa_perusahaan ?? null);

        if (!$nomorPerusahaan) {
            return $this->waFailed('Nomor WhatsApp perusahaan kandidat kosong atau tidak valid.', [
                'jadwal_ol_id' => $jadwalOl->id ?? null,
                'target' => $target,
                'kandidat_id' => $kandidat->id ?? null,
                'nama_kandidat' => $kandidat->nama_lengkap ?? null,
                'perusahaan_id' => $kandidat->perusahaan_id ?? null,
                'perusahaan' => $kandidat->nama_perusahaan ?? null,
                'no_wa_perusahaan' => $kandidat->no_wa_perusahaan ?? null,
            ]);
        }

        $openWaSession = $this->checkOpenWaSessionForSending();

        if (!($openWaSession['success'] ?? false)) {
            return $this->waFailed($openWaSession['message'] ?? 'Session OpenWA belum siap.', [
                'jadwal_ol_id' => $jadwalOl->id ?? null,
                'target' => $target,
                'kandidat_id' => $kandidat->id ?? null,
                'nama_kandidat' => $kandidat->nama_lengkap ?? null,
                'perusahaan_id' => $kandidat->perusahaan_id ?? null,
                'perusahaan' => $kandidat->nama_perusahaan ?? null,
                'nomor_perusahaan' => $nomorPerusahaan,
                'openwa_session' => $openWaSession,
            ]);
        }

        $deviceNumber = $this->normalizeWhatsappNumber($openWaSession['device_number'] ?? null);

        if ($deviceNumber && $deviceNumber !== $nomorPerusahaan) {
            return $this->waFailed('Nomor OpenWA aktif tidak sesuai dengan nomor WhatsApp perusahaan kandidat.', [
                'jadwal_ol_id' => $jadwalOl->id ?? null,
                'target' => $target,
                'kandidat_id' => $kandidat->id ?? null,
                'nama_kandidat' => $kandidat->nama_lengkap ?? null,
                'perusahaan_id' => $kandidat->perusahaan_id ?? null,
                'perusahaan' => $kandidat->nama_perusahaan ?? null,
                'nomor_perusahaan' => $nomorPerusahaan,
                'openwa_device_number' => $deviceNumber,
                'openwa_session' => $openWaSession,
            ]);
        }

        $message = $this->buildPesanJadwalOl($jadwalOl, $kandidat, $isUpdate);
        $sendResult = $this->sendOpenWaText($target, $message);

        return [
            'success' => (bool) ($sendResult['success'] ?? false),
            'message' => ($sendResult['success'] ?? false)
                ? 'Pesan jadwal Offering Letter berhasil dikirim ke kandidat melalui OpenWA.'
                : ($sendResult['message'] ?? 'Pesan jadwal Offering Letter gagal dikirim melalui OpenWA.'),
            'target' => $target,
            'chat_id' => $sendResult['chat_id'] ?? ($target . '@c.us'),
            'kandidat_id' => $kandidat->id ?? null,
            'nama_kandidat' => $kandidat->nama_lengkap ?? null,
            'perusahaan_id' => $kandidat->perusahaan_id ?? null,
            'perusahaan' => $kandidat->nama_perusahaan ?? null,
            'nomor_perusahaan' => $nomorPerusahaan,
            'openwa_session' => $openWaSession,
            'openwa_response' => $sendResult['response'] ?? null,
            'openwa_http_code' => $sendResult['http_code'] ?? null,
        ];
    }

    private function buildPesanJadwalOl(JadwalOfferingLetter $jadwalOl, object $kandidat, bool $isUpdate = false): string
    {
        $nama = $kandidat->nama_panggil ?: ($kandidat->nama_lengkap ?: 'Kandidat');
        $perusahaan = $kandidat->nama_perusahaan ?: '-';
        $posisi = $kandidat->nama_posisi ?: '-';
        $tanggal = $this->formatTanggalIndonesia($jadwalOl->tanggal_ol);
        $jam = $this->formatJam($jadwalOl->jam_ol);
        $metode = $jadwalOl->metode ?: '-';
        $pic = $jadwalOl->pic ?: '-';
        $link = trim((string) ($jadwalOl->link ?? ''));
        $catatan = trim((string) ($jadwalOl->catatan ?? ''));

        $judul = $isUpdate
            ? 'Informasi perubahan jadwal Offering Letter'
            : 'Informasi jadwal Offering Letter';

        $linkText = $link !== ''
            ? "Link: {$link}\n"
            : '';

        $catatanText = $catatan !== ''
            ? "Catatan: {$catatan}\n"
            : '';

        return "Halo {$nama},\n\n"
            . "{$judul} untuk posisi {$posisi} di {$perusahaan}.\n\n"
            . "Detail Offering Letter:\n"
            . "Tanggal: {$tanggal}\n"
            . "Jam: {$jam} WIB\n"
            . "Metode: {$metode}\n"
            . "PIC: {$pic}\n"
            . $linkText
            . $catatanText
            . "\nMohon hadir/bergabung sesuai jadwal yang sudah ditentukan.\n"
            . "Jika ada kendala, silakan hubungi WhatsApp ini.\n\n"
            . "Terima kasih.\n"
            . "Tim Rekrutmen {$perusahaan}";
    }
private function getDataKandidatUntukWa(?HasilReviewManagement $review): ?object
{
    $kandidatId = $this->getKandidatIdFromReview($review);

    if (!$kandidatId) {
        return null;
    }

    $query = DB::table('data_riwayat_diri as drd')
        ->leftJoin('posisi as p', 'p.id', '=', 'drd.posisi_yang_dilamar')
        ->leftJoin('data_perusahaan as dp', 'dp.id', '=', 'drd.perusahaan_dilamar')
        ->where('drd.id', $kandidatId);

    if (Schema::hasColumn('data_riwayat_diri', 'deleted_at')) {
        $query->whereNull('drd.deleted_at');
    }

    return $query
        ->select([
            'drd.id',
            'drd.nama_lengkap',
            'drd.nama_panggil',
            'drd.no_wa',
            'drd.email',
            'drd.posisi_yang_dilamar',
            'drd.perusahaan_dilamar',
            'p.nama_posisi',
            'dp.id as perusahaan_id',
            'dp.kode as perusahaan_kode',
            'dp.nama_perusahaan',
            'dp.no_wa as no_wa_perusahaan',
            'dp.token_api_wa',
        ])
        ->first();
}


    private function getKandidatIdFromReview(?HasilReviewManagement $review): ?string
    {
        if (!$review) {
            return null;
        }

        $fromRelation = $review->hasilInterview?->kandidat?->id ?? null;

        if ($fromRelation) {
            return (string) $fromRelation;
        }

        if (!empty($review->hasil_interview_id)) {
            $id = DB::table('jadwal_interview_kandidat')
                ->where('id', $review->hasil_interview_id)
                ->value('data_riwayat_diri_id');

            if ($id) {
                return (string) $id;
            }
        }

        if (
            Schema::hasColumn('hasil_review_management', 'daftar_hadir_test_zoom_id') &&
            !empty($review->daftar_hadir_test_zoom_id)
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
            !empty($review->daftar_hadir_test_mmpi_id)
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
        if (!$item) {
            return '-';
        }

        $namaFromRelation = $item->hasilInterview?->kandidat?->nama_lengkap;

        if ($namaFromRelation) {
            return $namaFromRelation;
        }

        $kandidatId = $this->getKandidatIdFromReview($item);

        if (!$kandidatId) {
            return '-';
        }

        return DB::table('data_riwayat_diri')
            ->where('id', $kandidatId)
            ->value('nama_lengkap') ?: '-';
    }

    private function makeKandidatLabel(?HasilReviewManagement $item): string
    {
        if (!$item) {
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

        if (!empty($review->hasil_interview_id)) {
            return 'Interview';
        }

        if (Schema::hasColumn('hasil_review_management', 'daftar_hadir_test_zoom_id') && !empty($review->daftar_hadir_test_zoom_id)) {
            return 'Hasil Test Zoom';
        }

        if (Schema::hasColumn('hasil_review_management', 'daftar_hadir_test_mmpi_id') && !empty($review->daftar_hadir_test_mmpi_id)) {
            return 'Hasil Test MMPI';
        }

        return '-';
    }

    private function normalizeWhatsappNumber($value): ?string
    {
        if (!$value) {
            return null;
        }

        $number = preg_replace('/[^0-9]/', '', (string) $value);

        if (!$number) {
            return null;
        }

        if (str_starts_with($number, '0')) {
            $number = '62' . substr($number, 1);
        } elseif (str_starts_with($number, '8')) {
            $number = '62' . $number;
        }

        return strlen($number) >= 10 ? $number : null;
    }

    private function normalizeTokenApiWa($value): ?string
    {
        $token = trim((string) ($value ?? ''));

        return $token !== '' ? $token : null;
    }

    private function formatTanggalIndonesia($value): string
    {
        if (!$value) {
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
        if (!$value) {
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
    
        if (!is_array($allowedPerusahaanIds)) {
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
    
        if (!is_array($allowedPerusahaanIds)) {
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
    
        if (!is_array($allowedPerusahaanIds)) {
            return true;
        }
    
        if (empty($allowedPerusahaanIds)) {
            return false;
        }
    
        $kandidat = $this->getDataKandidatUntukWa($review);
    
        if (!$kandidat || empty($kandidat->perusahaan_id)) {
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
                    ->map(fn ($id) => (string) $id)
                    ->values()
                    ->all();
            }
        } catch (\Throwable $e) {
            $ids = [];
        }
    
        if (empty($ids) && !empty($user->perusahaan_id)) {
            $ids[] = (string) $user->perusahaan_id;
        }
    
        if (empty($ids) && !empty($user->data_perusahaan_id)) {
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

    private function openWaBaseUrl(): string
    {
        $url = rtrim((string) config('services.waha.url', env('WAHA_URL', 'https://wa.blast.dsicorp.id')), '/');

        // WAHA_URL boleh diisi domain utama atau sudah dengan /api.
        // Helper ini memastikan URL final hanya punya satu /api.
        if (!str_ends_with($url, '/api')) {
            $url .= '/api';
        }

        return $url;
    }

    private function openWaSessionName(): ?string
    {
        $sessionName = trim((string) config('services.waha.session', env('WAHA_SESSION', 'rekruitment')));

        return $sessionName !== '' ? $sessionName : null;
    }

    private function openWaSessionUuid(): ?string
    {
        $sessionUuid = trim((string) config('services.waha.session_id', env('WAHA_SESSION_ID', '')));

        return $sessionUuid !== '' ? $sessionUuid : null;
    }

    private function openWaSessionId(): ?string
    {
        // Untuk endpoint kirim pesan, gunakan UUID WAHA_SESSION_ID.
        // Jika kosong, fallback ke nama session agar tidak memutus konfigurasi lama.
        return $this->openWaSessionUuid() ?: $this->openWaSessionName();
    }

    private function openWaHeaders(): array
    {
        $apiKey = trim((string) config('services.waha.api_key', env('WAHA_API_KEY', '')));

        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        if ($apiKey !== '') {
            // WAHA memakai X-Api-Key. Hindari X-API-Key agar konsisten dengan controller lain.
            $headers['X-Api-Key'] = $apiKey;
        }

        return $headers;
    }

    private function checkOpenWaSessionForSending(): array
    {
        $baseUrl = $this->openWaBaseUrl();
        $sessionName = $this->openWaSessionName();
        $sessionUuid = $this->openWaSessionUuid();
        $sendSessionId = $this->openWaSessionId();

        if (!$sessionName && !$sessionUuid) {
            return [
                'success' => false,
                'session' => null,
                'session_name' => null,
                'session_id' => null,
                'send_session_id' => null,
                'status' => 'error',
                'message' => 'WAHA_SESSION / WAHA_SESSION_ID belum diatur di file .env.',
                'device_number' => null,
                'device_status' => null,
            ];
        }

        $url = $baseUrl . '/sessions';

        try {
            $response = Http::withoutVerifying()
                ->withHeaders($this->openWaHeaders())
                ->timeout(30)
                ->get($url);

            $body = $response->body();
            $json = $response->json();

            Log::info('OpenWA session check Offering Letter', [
                'url' => $url,
                'session_name_env' => $sessionName,
                'session_id_env' => $sessionUuid,
                'send_session_id' => $sendSessionId,
                'http_code' => $response->status(),
                'response_json' => $json,
                'response_body' => $body,
            ]);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'session' => $sessionName ?: $sendSessionId,
                    'session_name' => $sessionName,
                    'session_id' => $sessionUuid,
                    'send_session_id' => $sendSessionId,
                    'status' => 'error',
                    'message' => 'Gagal mengecek session OpenWA. HTTP Code: ' . $response->status() . '. Response: ' . ($body ?: json_encode($json)),
                    'device_number' => null,
                    'device_status' => null,
                    'openwa_response' => $json ?: $body,
                ];
            }

            $sessionData = $this->extractOpenWaSessionData($json, $sessionName, $sessionUuid);

            if (empty($sessionData)) {
                return [
                    'success' => false,
                    'session' => $sessionName ?: $sendSessionId,
                    'session_name' => $sessionName,
                    'session_id' => $sessionUuid,
                    'send_session_id' => $sendSessionId,
                    'status' => 'not_found',
                    'message' => 'Session OpenWA tidak ditemukan. Pastikan WAHA_SESSION adalah nama session dan WAHA_SESSION_ID adalah UUID session.',
                    'device_number' => null,
                    'device_status' => null,
                    'openwa_response' => $json,
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
                'ready',
                'connected',
                'connect',
                'working',
                'authenticated',
            ], true);

            if (!$isConnected) {
                return [
                    'success' => false,
                    'session' => $sessionName ?: $sendSessionId,
                    'session_name' => $sessionName,
                    'session_id' => $sessionUuid,
                    'send_session_id' => $sendSessionId,
                    'status' => 'disconnected',
                    'message' => 'Session OpenWA belum ready. Status saat ini: ' . ($deviceStatus ?: '-'),
                    'device_number' => $deviceNumber,
                    'device_status' => $deviceStatus ?: null,
                    'openwa_response' => $json,
                ];
            }

            return [
                'success' => true,
                'session' => $sessionName ?: $sendSessionId,
                'session_name' => $sessionName,
                'session_id' => $sessionUuid,
                'send_session_id' => $sendSessionId,
                'status' => 'connected',
                'message' => 'Session OpenWA ready.',
                'device_number' => $deviceNumber,
                'device_status' => $deviceStatus,
                'openwa_response' => $json,
            ];
        } catch (\Throwable $e) {
            Log::error('Gagal mengecek session OpenWA Offering Letter', [
                'url' => $url,
                'session_name_env' => $sessionName,
                'session_id_env' => $sessionUuid,
                'send_session_id' => $sendSessionId,
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'session' => $sessionName ?: $sendSessionId,
                'session_name' => $sessionName,
                'session_id' => $sessionUuid,
                'send_session_id' => $sendSessionId,
                'status' => 'error',
                'message' => 'Gagal mengecek session OpenWA: ' . $e->getMessage(),
                'device_number' => null,
                'device_status' => null,
            ];
        }
    }

    private function sendOpenWaText(string $target, string $message): array
    {
        $sessionId = $this->openWaSessionId();
        $chatId = $target . '@c.us';

        if (!$sessionId) {
            return [
                'success' => false,
                'chat_id' => $chatId,
                'session_id_used_for_send' => null,
                'http_code' => null,
                'response' => null,
                'message' => 'WAHA_SESSION_ID / WAHA_SESSION belum diatur di file .env.',
            ];
        }

        $url = $this->openWaBaseUrl() . '/sessions/' . rawurlencode($sessionId) . '/messages/send-text';
        $payload = [
            'chatId' => $chatId,
            'text' => $message,
        ];

        try {
            $response = Http::withoutVerifying()
                ->withHeaders($this->openWaHeaders())
                ->timeout(60)
                ->post($url, $payload);

            $body = $response->body();
            $json = $response->json();

            Log::info('OpenWA send Offering Letter message', [
                'url' => $url,
                'target' => $target,
                'chat_id' => $chatId,
                'session_id_used_for_send' => $sessionId,
                'session_name_env' => $this->openWaSessionName(),
                'session_id_env' => $this->openWaSessionUuid(),
                'payload' => $payload,
                'http_code' => $response->status(),
                'response_json' => $json,
                'response_body' => $body,
            ]);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'chat_id' => $chatId,
                    'session_id_used_for_send' => $sessionId,
                    'http_code' => $response->status(),
                    'response' => $json ?: $body,
                    'message' => 'Gagal mengirim pesan melalui OpenWA. HTTP Code: ' . $response->status() . '. Session yang dipakai: ' . $sessionId . '. Response: ' . ($body ?: json_encode($json)),
                ];
            }

            return [
                'success' => true,
                'chat_id' => $chatId,
                'session_id_used_for_send' => $sessionId,
                'http_code' => $response->status(),
                'response' => $json ?: $body,
                'message' => 'Pesan berhasil dikirim melalui OpenWA.',
            ];
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim pesan OpenWA Offering Letter', [
                'url' => $url,
                'target' => $target,
                'chat_id' => $chatId,
                'session_id_used_for_send' => $sessionId,
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'chat_id' => $chatId,
                'session_id_used_for_send' => $sessionId,
                'http_code' => null,
                'response' => null,
                'message' => 'Gagal mengirim pesan melalui OpenWA: ' . $e->getMessage(),
            ];
        }
    }

    private function extractOpenWaSessionData($json, ?string $sessionName = null, ?string $sessionUuid = null): array
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
                    ($sessionName && ($itemName === $sessionName || $itemSession === $sessionName)) ||
                    ($sessionUuid && ($itemId === $sessionUuid || $itemSessionId === $sessionUuid || $itemUuid === $sessionUuid || $itemSession === $sessionUuid))
                ) {
                    return $item;
                }
            }

            return [];
        }

        if (is_array($json)) {
            return $json;
        }

        return [];
    }

    private function extractOpenWaPhoneNumber(array $sessionData): ?string
    {
        $candidates = [
            $sessionData['phone'] ?? null,
            $sessionData['phoneNumber'] ?? null,
            $sessionData['phone_number'] ?? null,
            $sessionData['me']['id'] ?? null,
            $sessionData['me']['user'] ?? null,
            $sessionData['me']['number'] ?? null,
            $sessionData['me']['phone'] ?? null,
            $sessionData['me']['wid']['user'] ?? null,
            $sessionData['account']['id'] ?? null,
            $sessionData['account']['user'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (is_array($candidate)) {
                $nested = $this->extractOpenWaPhoneNumber($candidate);

                if ($nested) {
                    return $nested;
                }

                continue;
            }

            $normalized = $this->normalizeWhatsappNumber($candidate);

            if ($normalized) {
                return $normalized;
            }
        }

        return null;
    }

    private function waFailed(string $message, array $extra = []): array
    {
        return array_merge([
            'success' => false,
            'message' => $message,
            'target' => null,
            'openwa_http_code' => null,
            'openwa_response' => null,
        ], $extra);
    }
}
