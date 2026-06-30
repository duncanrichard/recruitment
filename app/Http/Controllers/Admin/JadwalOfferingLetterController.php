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
use Illuminate\Validation\Rule;

class JadwalOfferingLetterController extends Controller
{
    private string $timezone = 'Asia/Jakarta';

    public function list()
    {
        $data = JadwalOfferingLetter::with([
                'hasilReviewManagement.hasilInterview.kandidat',
            ])
            ->latest()
            ->get()
            ->map(function ($item) {
                $hasilReview = $item->hasilReviewManagement;

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
        $data = HasilReviewManagement::with([
                'hasilInterview.kandidat',
            ])
            ->whereRaw('LOWER(status) = ?', ['diterima'])
            ->whereDoesntHave('jadwalOfferingLetter')
            ->latest()
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'hasil_interview_id' => $item->hasil_interview_id,
                    'review_management' => $item->review_management,
                    'status' => $item->status,
                    'nama_kandidat' => $this->getNamaKandidat($item),
                    'label' => $this->makeKandidatLabel($item),
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
        $data = JadwalOfferingLetter::findOrFail($id);

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
        $data = JadwalOfferingLetter::findOrFail($id);

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
        $data = JadwalOfferingLetter::findOrFail($id);

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
            return $this->waFailed('Data review management tidak ditemukan.');
        }

        $kandidat = $this->getDataKandidatUntukWa($review);

        if (!$kandidat) {
            return $this->waFailed('Data kandidat tidak ditemukan dari review management.', [
                'hasil_review_management_id' => $review->id ?? null,
            ]);
        }

        $target = $this->normalizeWhatsappNumber($kandidat->no_wa ?? null);

        if (!$target) {
            return $this->waFailed('Nomor WhatsApp kandidat kosong atau tidak valid.', [
                'kandidat_id' => $kandidat->id ?? null,
                'nama_kandidat' => $kandidat->nama_lengkap ?? null,
                'no_wa' => $kandidat->no_wa ?? null,
            ]);
        }

        $tokenApiWa = $this->normalizeTokenApiWa($kandidat->token_api_wa ?? null);

        if (!$tokenApiWa) {
            return $this->waFailed('Token API WA perusahaan kandidat kosong.', [
                'kandidat_id' => $kandidat->id ?? null,
                'nama_kandidat' => $kandidat->nama_lengkap ?? null,
                'perusahaan_id' => $kandidat->perusahaan_id ?? null,
                'perusahaan' => $kandidat->nama_perusahaan ?? null,
            ]);
        }

        $message = $this->buildPesanJadwalOl($jadwalOl, $kandidat, $isUpdate);

        try {
            $response = Http::asForm()
                ->withoutVerifying()
                ->withHeaders([
                    'Authorization' => $tokenApiWa,
                ])
                ->timeout(120)
                ->post('https://api.fonnte.com/send', [
                    'target' => $target,
                    'message' => $message,
                    'countryCode' => '62',
                    'typing' => 'false',
                    'preview' => 'true',
                ]);

            $json = $response->json();
            $fonnteStatus = $json['status'] ?? $json['Status'] ?? false;
            $isSuccess = $response->successful() && (bool) $fonnteStatus;

            return [
                'success' => $isSuccess,
                'message' => $isSuccess
                    ? 'Pesan jadwal Offering Letter berhasil dikirim ke kandidat.'
                    : ($json['reason'] ?? $json['detail'] ?? $json['message'] ?? 'Pesan jadwal Offering Letter gagal dikirim melalui Fonnte.'),
                'target' => $target,
                'kandidat_id' => $kandidat->id ?? null,
                'nama_kandidat' => $kandidat->nama_lengkap ?? null,
                'perusahaan_id' => $kandidat->perusahaan_id ?? null,
                'perusahaan' => $kandidat->nama_perusahaan ?? null,
                'fonnte_http_code' => $response->status(),
                'fonnte_response' => $json ?: $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim pesan jadwal Offering Letter ke kandidat', [
                'message' => $e->getMessage(),
                'jadwal_ol_id' => $jadwalOl->id ?? null,
                'hasil_review_management_id' => $review->id ?? null,
                'kandidat_id' => $kandidat->id ?? null,
            ]);

            return $this->waFailed('Terjadi kesalahan saat mengirim pesan WhatsApp: ' . $e->getMessage(), [
                'target' => $target,
                'kandidat_id' => $kandidat->id ?? null,
                'nama_kandidat' => $kandidat->nama_lengkap ?? null,
            ]);
        }
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

    private function getDataKandidatUntukWa(HasilReviewManagement $review): ?object
    {
        $kandidatId = $this->getKandidatIdFromReview($review);

        if (!$kandidatId) {
            return null;
        }

        return DB::table('data_riwayat_diri as drd')
            ->leftJoin('posisi as p', 'p.id', '=', 'drd.posisi_yang_dilamar')
            ->leftJoin('data_perusahaan as dp', 'dp.id', '=', 'drd.perusahaan_dilamar')
            ->where('drd.id', $kandidatId)
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

    private function waFailed(string $message, array $extra = []): array
    {
        return array_merge([
            'success' => false,
            'message' => $message,
            'target' => null,
            'fonnte_http_code' => null,
            'fonnte_response' => null,
        ], $extra);
    }
}
