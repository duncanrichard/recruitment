<?php

namespace App\Http\Controllers;

use App\Models\DataRiwayatDiri;
use App\Models\JadwalTestZoom;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class CekTahapanPelamarController extends Controller
{
    private const PESAN_LOLOS_SELEKSI = "SELAMAT!\n\nAnda dinyatakan lolos tahap psikotes dan dapat melanjutkan ke tahap seleksi berikutnya.\n\nUntuk informasi dan proses selanjutnya, silakan melakukan pengecekan secara berkala melalui website ini.\n\nTerima kasih dan semoga sukses pada tahap berikutnya.";

    private const PESAN_LOLOS_OFFERING_LETTER = "Selamat, Anda lolos tahap interview dan saat ini sedang dalam proses offering letter.\n\nJadwal penyampaian offering letter akan kami informasikan lebih lanjut. Jika ada pertanyaan, silakan hubungi kami melalui WhatsApp.";

    private const PESAN_OFFERING_LETTER_MENERIMA = "Selamat, Anda telah menerima offering letter.\n\nSaat ini Anda dinyatakan siap untuk bekerja. Silakan mempersiapkan diri dan mengikuti arahan dari tim rekrutmen atau HR untuk proses onboarding dan informasi mulai bekerja.\n\nTerima kasih dan semoga sukses dalam perjalanan karier Anda bersama kami.";

    private const PESAN_OFFERING_LETTER_MENOLAK = "Terima kasih atas konfirmasi Anda.\n\nKami menghargai keputusan Anda untuk menolak offering letter yang telah diberikan. Semoga keputusan ini menjadi pilihan terbaik dan semoga Anda mendapatkan kesempatan karier yang lebih sesuai di masa depan.\n\nTetap semangat dan sukses selalu.";

    private const PESAN_OFFERING_LETTER_TIDAK_MELANJUTKAN = "Terima kasih sudah mengikuti proses seleksi sampai tahap offering letter.\n\nKami menghargai waktu, usaha, dan keputusan Anda untuk tidak melanjutkan proses ini. Semoga pengalaman ini tetap memberi manfaat dan semoga Anda mendapatkan kesempatan terbaik dalam perjalanan karier berikutnya.\n\nTetap semangat dan sukses selalu.";

    private const PESAN_TIDAK_LOLOS_SELEKSI = "Terima kasih telah mengikuti proses seleksi di perusahaan kami.\n\nSetelah melalui proses evaluasi, kami belum dapat melanjutkan Anda ke tahap berikutnya. Kami mengapresiasi waktu dan usaha yang telah diberikan.\n\nSemoga sukses dan lancar dalam perjalanan karier Anda ke depan. Terima kasih.";

    private function pelamarQuery()
    {
        return DataRiwayatDiri::query()
            ->with([
                'posisi',
                'perusahaan',
                'jadwalTestZoom',
                'riwayatKeluarga',
                'saudaraKandung',
                'saudaraIpar',
                'riwayatKesehatan',
                'riwayatPekerjaan',
                'kesiapanBekerja',
            ]);
    }

    public function show(string $token)
    {
        $pelamar = $this->pelamarQuery()
            ->where('token', $token)
            ->firstOrFail();

        return view('pages.pendaftaran.index', [
            'title' => 'Cek Tahapan Seleksi',
            'token' => $token,
            'pelamar' => null,
            'hasil_tahapan' => $this->buildTahapanPelamar($pelamar),
        ]);
    }

    public function tahapanByToken(string $token): JsonResponse
    {
        $pelamar = $this->pelamarQuery()
            ->where('token', $token)
            ->first();

        if (!$pelamar) {
            return response()->json([
                'success' => false,
                'message' => 'Token pelamar tidak ditemukan.',
                'errors' => [
                    'token' => 'Token pelamar tidak ditemukan.',
                ],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tahapan seleksi berhasil ditampilkan.',
            'data' => $this->buildTahapanPelamar($pelamar),
        ]);
    }

    public function cekTahapanByToken(string $token): JsonResponse
    {
        return $this->tahapanByToken($token);
    }

    public function findTahapanByToken(string $token): JsonResponse
    {
        return $this->tahapanByToken($token);
    }

    public function updateKehadiranJadwalTest(
        Request $request,
        string $token,
        string $jadwalTest
    ): JsonResponse {
        $pelamar = $this->pelamarQuery()
            ->where('token', $token)
            ->first();

        if (!$pelamar) {
            return response()->json([
                'success' => false,
                'message' => 'Token pelamar tidak ditemukan.',
                'errors' => [
                    'token' => 'Token pelamar tidak ditemukan.',
                ],
            ], 404);
        }

        $validated = $request->validate([
            'kehadiran' => [
                'required',
                'string',
                Rule::in(['hadir', 'tidak_hadir']),
            ],
        ], [
            'kehadiran.required' => 'Pilihan kehadiran wajib diisi.',
            'kehadiran.in' => 'Pilihan kehadiran harus Hadir atau Tidak Hadir.',
        ]);

        $jadwal = JadwalTestZoom::query()
            ->where('id', $jadwalTest)
            ->where('data_riwayat_diri_id', $pelamar->id)
            ->first();

        if (!$jadwal) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal test Zoom tidak ditemukan.',
                'data' => $this->buildTahapanPelamar($pelamar),
            ], 404);
        }

        if (!$this->canAccessJadwalTestZoom($pelamar)) {
            return response()->json([
                'success' => false,
                'message' => 'Tahapan Test Zoom belum dapat dilanjutkan. Lengkapi terlebih dahulu seluruh data pendaftaran sampai tahap Kesiapan Bekerja.',
                'data' => $this->buildTahapanPelamar($pelamar),
            ], 423);
        }

        if (empty($jadwal->jadwal)) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal jadwal test Zoom belum tersedia.',
                'data' => $this->buildTahapanPelamar($pelamar),
            ], 422);
        }

        $jadwalCarbon = $this->parseDateTime($jadwal->jadwal);

        if (!$jadwalCarbon) {
            return response()->json([
                'success' => false,
                'message' => 'Format jadwal test Zoom tidak valid.',
                'data' => $this->buildTahapanPelamar($pelamar),
            ], 422);
        }

        if (!$jadwalCarbon->isToday()) {
            return response()->json([
                'success' => false,
                'message' => 'Kehadiran hanya dapat diisi pada tanggal jadwal test Zoom.',
                'data' => $this->buildTahapanPelamar($pelamar),
            ], 422);
        }

        $daftarHadir = $this->getDaftarHadirByJadwal($jadwal);
        $kehadiranLama = $this->normalizeKehadiranValue(
            $daftarHadir->status_kehadiran ?? null
        );

        if (!empty($kehadiranLama)) {
            $pelamarTerbaru = $this->pelamarQuery()
                ->where('token', $token)
                ->first();

            return response()->json([
                'success' => false,
                'message' => 'Status kehadiran sudah tersimpan dan tidak dapat diubah.',
                'data' => $this->buildTahapanPelamar($pelamarTerbaru),
            ], 409);
        }

        DB::transaction(function () use ($jadwal, $validated) {
            $this->saveDaftarHadirTestZoom($jadwal, $validated['kehadiran']);
        });

        $pelamarTerbaru = $this->pelamarQuery()
            ->where('token', $token)
            ->first();

        return response()->json([
            'success' => true,
            'message' => $validated['kehadiran'] === 'hadir'
                ? 'Kehadiran berhasil disimpan: Hadir. Link Zoom sudah bisa dibuka.'
                : 'Kehadiran berhasil disimpan: Tidak Hadir.',
            'data' => $this->buildTahapanPelamar($pelamarTerbaru),
        ]);
    }

    public function simpanKehadiranJadwalTestByToken(
        Request $request,
        string $token,
        string $jadwalTest
    ): JsonResponse {
        return $this->updateKehadiranJadwalTest($request, $token, $jadwalTest);
    }

    public function updateKehadiranJadwalTestMmpi(
        Request $request,
        string $token,
        string $jadwalTestMmpi
    ): JsonResponse {
        $pelamar = $this->pelamarQuery()
            ->where('token', $token)
            ->first();

        if (!$pelamar) {
            return response()->json([
                'success' => false,
                'message' => 'Token pelamar tidak ditemukan.',
                'errors' => [
                    'token' => 'Token pelamar tidak ditemukan.',
                ],
            ], 404);
        }

        $validated = $request->validate([
            'kehadiran' => [
                'required',
                'string',
                Rule::in(['hadir', 'tidak_hadir']),
            ],
        ], [
            'kehadiran.required' => 'Pilihan kehadiran wajib diisi.',
            'kehadiran.in' => 'Pilihan kehadiran harus Hadir atau Tidak Hadir.',
        ]);

        if (!Schema::hasTable('jadwal_test_mmpi')) {
            return response()->json([
                'success' => false,
                'message' => 'Tabel jadwal test MMPI tidak ditemukan.',
                'data' => $this->buildTahapanPelamar($pelamar),
            ], 500);
        }

        $query = DB::table('jadwal_test_mmpi as jtm')
            ->where('jtm.id', $jadwalTestMmpi)
            ->where('jtm.data_riwayat_diri_id', $pelamar->id);

        if (Schema::hasColumn('jadwal_test_mmpi', 'deleted_at')) {
            $query->whereNull('jtm.deleted_at');
        }

        $jadwalMmpi = $query->first();

        if (!$jadwalMmpi) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal test MMPI tidak ditemukan.',
                'data' => $this->buildTahapanPelamar($pelamar),
            ], 404);
        }

        if (empty($jadwalMmpi->tanggal)) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal jadwal test MMPI belum tersedia.',
                'data' => $this->buildTahapanPelamar($pelamar),
            ], 422);
        }

        $jadwalCarbon = $this->parseDateTime($jadwalMmpi->tanggal);

        if (!$jadwalCarbon) {
            return response()->json([
                'success' => false,
                'message' => 'Format jadwal test MMPI tidak valid.',
                'data' => $this->buildTahapanPelamar($pelamar),
            ], 422);
        }

        if (!$jadwalCarbon->isToday()) {
            return response()->json([
                'success' => false,
                'message' => 'Kehadiran Test MMPI hanya dapat diisi pada tanggal jadwal test.',
                'data' => $this->buildTahapanPelamar($pelamar),
            ], 422);
        }

        $daftarHadirMmpi = $this->getDaftarHadirByJadwalMmpi($jadwalMmpi);
        $kehadiranLama = $this->normalizeKehadiranValue(
            $daftarHadirMmpi->status_kehadiran ?? null
        );

        if (!empty($kehadiranLama)) {
            $pelamarTerbaru = $this->pelamarQuery()
                ->where('token', $token)
                ->first();

            return response()->json([
                'success' => false,
                'message' => 'Status kehadiran Test MMPI sudah tersimpan dan tidak dapat diubah.',
                'data' => $this->buildTahapanPelamar($pelamarTerbaru),
            ], 409);
        }

        DB::transaction(function () use ($jadwalMmpi, $validated) {
            $this->saveDaftarHadirTestMmpi($jadwalMmpi, $validated['kehadiran']);
        });

        $pelamarTerbaru = $this->pelamarQuery()
            ->where('token', $token)
            ->first();

        return response()->json([
            'success' => true,
            'message' => $validated['kehadiran'] === 'hadir'
                ? 'Kehadiran Test MMPI berhasil disimpan: Hadir.'
                : 'Kehadiran Test MMPI berhasil disimpan: Tidak Hadir.',
            'data' => $this->buildTahapanPelamar($pelamarTerbaru),
        ]);
    }

    public function simpanKehadiranJadwalTestMmpiByToken(
        Request $request,
        string $token,
        string $jadwalTestMmpi
    ): JsonResponse {
        return $this->updateKehadiranJadwalTestMmpi($request, $token, $jadwalTestMmpi);
    }

    private function buildTahapanPelamar(?DataRiwayatDiri $pelamar): array
    {
        if (!$pelamar) {
            return [
                'status' => 'Data Tidak Ditemukan',
                'keterangan' => 'Data pelamar tidak ditemukan.',
                'saran' => 'Silakan periksa kembali token pelamar.',
                'tahapan_terakhir' => '-',
                'nama_pelamar' => '-',
                'posisi_dilamar' => '-',
                'perusahaan_dilamar' => '-',
                'token' => null,
                'jadwal_test' => null,
                'jadwal_test_zoom' => null,
                'jadwalTestZoom' => null,
                'jadwal_test_mmpi' => null,
                'jadwalTestMmpi' => null,
                'jadwal_interview' => null,
                'jadwalInterview' => null,
                'hasil_interview' => null,
                'hasilInterview' => null,
                'status_kehadiran_interview' => null,
                'statusKehadiranInterview' => null,
                'catatan_interview' => null,
                'catatanInterview' => null,
                'review_management_id' => null,
                'reviewManagementId' => null,
                'hasil_review_management_id' => null,
                'hasilReviewManagementId' => null,
                'review_management' => null,
                'reviewManagement' => null,
                'status_review_management' => null,
                'statusReviewManagement' => null,
                'status_review' => null,
                'statusReview' => null,
                'jadwal_offering_letter' => null,
                'jadwalOfferingLetter' => null,
                'status_jadwal_offering_letter' => null,
                'statusJadwalOfferingLetter' => null,
                'hasil_test' => null,
                'hasilTest' => null,
                'hasil_test_mmpi' => null,
                'hasilTestMmpi' => null,
                'status_hasil_test_mmpi' => null,
                'statusHasilTestMmpi' => null,
                'tahapan' => [],
            ];
        }

        $pelamar->loadMissing([
            'posisi',
            'perusahaan',
            'jadwalTestZoom',
            'riwayatKeluarga',
            'saudaraKandung',
            'saudaraIpar',
            'riwayatKesehatan',
            'riwayatPekerjaan',
            'kesiapanBekerja',
        ]);

        $formCompletion = $this->calculateFormCompletion($pelamar);
        $bolehLanjutJadwalTestZoom = (int) $formCompletion['completed_steps'] >= (int) $formCompletion['total_steps'];

        $jadwalTest = JadwalTestZoom::query()
            ->where('data_riwayat_diri_id', $pelamar->id)
            ->orderByDesc('jadwal')
            ->first();

        $jadwalTestData = $this->formatJadwalTest($jadwalTest);
        $jadwalTestMmpiData = $this->formatJadwalTestMmpi($pelamar);
        $jadwalInterviewData = $this->formatJadwalInterview($pelamar);

        $hasilTest = $jadwalTestData['hasil_test'] ?? null;
        $hasilTestMmpi = $jadwalTestMmpiData['hasil_test'] ?? null;
        $hasilInterview = $jadwalInterviewData['hasil_interview'] ?? null;
        $statusKehadiranInterview = $jadwalInterviewData['kehadiran'] ?? null;
        $statusReviewManagement = $jadwalInterviewData['status_review_management'] ?? null;
        $reviewManagement = $jadwalInterviewData['review_management'] ?? null;
        $jadwalOfferingLetterData = $jadwalInterviewData['jadwal_offering_letter'] ?? null;
        $statusJadwalOfferingLetter = $jadwalInterviewData['status_jadwal_offering_letter'] ?? null;

        $isInterviewReschedule = $statusKehadiranInterview === 'reschedule';

        $punyaJadwalTest = !empty($jadwalTestData);
        $punyaHasilTest = !empty($hasilTest);
        $punyaJadwalTestMmpi = !empty($jadwalTestMmpiData);
        $punyaHasilTestMmpi = !empty($hasilTestMmpi);
        $punyaJadwalInterview = !empty($jadwalInterviewData);
        $punyaHasilInterview = !empty($hasilInterview);
        $punyaReviewManagementRow = !empty($jadwalInterviewData['hasil_review_management_id'] ?? null);
        $punyaJadwalOfferingLetter = !empty($jadwalOfferingLetterData);

        $hasilInterviewGagal = $hasilInterview === 'tidak_lolos_interview';
        $hasilInterviewLanjutReview = in_array($hasilInterview, ['lolos_interview', 'dipertimbangkan'], true);
        $reviewProses = $hasilInterviewLanjutReview && $punyaReviewManagementRow && empty($statusReviewManagement);
        $reviewDiterima = $hasilInterviewLanjutReview && $statusReviewManagement === 'diterima';
        $reviewGagal = $hasilInterviewLanjutReview && $statusReviewManagement === 'gagal';

        $pesanBelumLengkap = 'Tahapan Test Zoom belum dapat dilanjutkan karena data pendaftaran belum lengkap sampai tahap Kesiapan Bekerja. Silakan lengkapi seluruh formulir pendaftaran terlebih dahulu.';
        $tahapFormTerakhir = $formCompletion['last_completed_label'] ?? 'Data Diri';

        $tahapan = [
            [
                'nama' => 'Administrasi',
                'status' => $punyaJadwalTest && $bolehLanjutJadwalTestZoom ? 'Lolos' : 'Proses',
                'keterangan' => $punyaJadwalTest && $bolehLanjutJadwalTestZoom
                    ? 'Tahap Administrasi sudah selesai.'
                    : 'Kandidat sedang berada pada tahap Administrasi.',
                'saran' => $bolehLanjutJadwalTestZoom
                    ? ($punyaJadwalTest ? null : 'Silakan pantau halaman ini secara berkala untuk melihat perkembangan proses seleksi.')
                    : $pesanBelumLengkap,
            ],
        ];

        if ($punyaJadwalTest) {
            $jadwalTestPayload = array_merge($jadwalTestData, [
                'boleh_akses_jadwal_test_zoom' => $bolehLanjutJadwalTestZoom,
                'bolehAksesJadwalTestZoom' => $bolehLanjutJadwalTestZoom,
                'disabled' => !$bolehLanjutJadwalTestZoom,
                'disabled_reason' => $bolehLanjutJadwalTestZoom ? null : $pesanBelumLengkap,
                'disabledReason' => $bolehLanjutJadwalTestZoom ? null : $pesanBelumLengkap,
            ]);

            $tahapan[] = [
                'nama' => 'Jadwal Test Zoom',
                'status' => $bolehLanjutJadwalTestZoom ? 'Terjadwal' : 'Terkunci',
                'keterangan' => $bolehLanjutJadwalTestZoom
                    ? 'Jadwal test Zoom kandidat sudah tersedia.'
                    : 'Jadwal test Zoom sudah tersedia, namun belum dapat dilanjutkan karena formulir pendaftaran belum lengkap sampai tahap Kesiapan Bekerja.',
                'saran' => $bolehLanjutJadwalTestZoom
                    ? 'Silakan mengikuti test Zoom sesuai jadwal yang sudah ditentukan.'
                    : $pesanBelumLengkap,
                'jadwal_test' => $jadwalTestPayload,
                'jadwal_test_zoom' => $jadwalTestPayload,
            ];
        }

        if ($punyaHasilTest) {
            $tahapan[] = [
                'nama' => 'Hasil Seleksi Test Zoom',
                'status' => $hasilTest === 'lolos' ? 'Lolos' : 'Gagal',
                'keterangan' => $hasilTest === 'lolos'
                    ? self::PESAN_LOLOS_SELEKSI
                    : self::PESAN_TIDAK_LOLOS_SELEKSI,
                'saran' => null,
                'hasil_test' => $hasilTest,
                'hasilTest' => $hasilTest,
            ];

            if ($hasilTest === 'lolos') {
                $tahapan[] = [
                    'nama' => 'Jadwal Test MMPI',
                    'status' => $punyaJadwalTestMmpi ? 'Terjadwal' : 'Menunggu',
                    'keterangan' => $punyaJadwalTestMmpi
                        ? 'Jadwal test MMPI kandidat sudah tersedia.'
                        : 'Kandidat sudah lolos test Zoom dan sedang menunggu jadwal test MMPI.',
                    'saran' => $punyaJadwalTestMmpi
                        ? 'Silakan mengikuti test MMPI sesuai jadwal yang sudah ditentukan.'
                        : 'Silakan pantau halaman ini secara berkala untuk informasi jadwal test MMPI.',
                    'jadwal_test_mmpi' => $jadwalTestMmpiData,
                    'jadwalTestMmpi' => $jadwalTestMmpiData,
                ];
            }
        }

        if ($punyaHasilTestMmpi) {
            $tahapan[] = [
                'nama' => 'Hasil Seleksi Test MMPI',
                'status' => $hasilTestMmpi === 'lolos' ? 'Lolos' : 'Gagal',
                'keterangan' => $hasilTestMmpi === 'lolos'
                    ? self::PESAN_LOLOS_SELEKSI
                    : self::PESAN_TIDAK_LOLOS_SELEKSI,
                'saran' => null,
                'hasil_test' => $hasilTestMmpi,
                'hasilTest' => $hasilTestMmpi,
                'hasil_test_mmpi' => $hasilTestMmpi,
                'hasilTestMmpi' => $hasilTestMmpi,
            ];

            if ($hasilTestMmpi === 'lolos') {
                $tahapan[] = [
                    'nama' => 'Jadwal Interview',
                    'status' => $punyaJadwalInterview
                        ? ($isInterviewReschedule ? 'Reschedule' : 'Terjadwal')
                        : 'Menunggu',
                    'keterangan' => $punyaJadwalInterview
                        ? ($isInterviewReschedule
                            ? 'Jadwal interview kandidat sedang dalam proses penjadwalan ulang.'
                            : 'Jadwal interview kandidat sudah tersedia.')
                        : 'Kandidat sudah lolos test MMPI dan sedang menunggu jadwal interview.',
                    'saran' => $punyaJadwalInterview
                        ? ($isInterviewReschedule
                            ? 'Silakan pantau informasi jadwal interview terbaru dari tim rekrutmen.'
                            : 'Silakan mengikuti interview sesuai jadwal yang sudah ditentukan.')
                        : 'Silakan pantau halaman ini secara berkala untuk informasi jadwal interview.',
                    'jadwal_interview' => $jadwalInterviewData,
                    'jadwalInterview' => $jadwalInterviewData,
                ];
            }
        }

        if ($punyaHasilInterview) {
            if ($hasilInterviewGagal) {
                $tahapan[] = [
                    'nama' => 'Interview',
                    'status' => 'Gagal Interview',
                    'keterangan' => self::PESAN_TIDAK_LOLOS_SELEKSI,
                    'saran' => null,
                    'hasil_interview' => $hasilInterview,
                    'hasilInterview' => $hasilInterview,
                    'catatan_interview' => $jadwalInterviewData['catatan'] ?? null,
                    'catatanInterview' => $jadwalInterviewData['catatan'] ?? null,
                ];
            } elseif ($hasilInterviewLanjutReview) {
                $statusInterview = $reviewProses || (!$reviewDiterima && !$reviewGagal)
                    ? 'Review Management'
                    : ($reviewDiterima ? 'Lolos Interview' : 'Gagal Interview');

                $keteranganInterview = $reviewProses || (!$reviewDiterima && !$reviewGagal)
                    ? 'Hasil interview sudah tersedia dan sedang diproses ke tahap Review Management.'
                    : ($reviewDiterima ? self::PESAN_LOLOS_SELEKSI : self::PESAN_TIDAK_LOLOS_SELEKSI);

                $saranInterview = $reviewProses || (!$reviewDiterima && !$reviewGagal)
                    ? 'Silakan pantau halaman ini secara berkala untuk melihat hasil Review Management.'
                    : null;

                $tahapan[] = [
                    'nama' => 'Interview',
                    'status' => $statusInterview,
                    'keterangan' => $keteranganInterview,
                    'saran' => $saranInterview,
                    'hasil_interview' => $hasilInterview,
                    'hasilInterview' => $hasilInterview,
                    'catatan_interview' => $jadwalInterviewData['catatan'] ?? null,
                    'catatanInterview' => $jadwalInterviewData['catatan'] ?? null,
                    'review_management' => $reviewManagement,
                    'reviewManagement' => $reviewManagement,
                    'status_review_management' => $statusReviewManagement,
                    'statusReviewManagement' => $statusReviewManagement,
                    'hasil_review_management_id' => $jadwalInterviewData['hasil_review_management_id'] ?? null,
                    'hasilReviewManagementId' => $jadwalInterviewData['hasil_review_management_id'] ?? null,
                ];

                if ($reviewDiterima) {
                    $tahapan[] = [
                        'nama' => 'Jadwal Offering Letter',
                        'status' => $punyaJadwalOfferingLetter
                            ? ($statusJadwalOfferingLetter ?: 'Pending')
                            : 'Pending',
                        'keterangan' => $punyaJadwalOfferingLetter
                            ? 'Jadwal Offering Letter sudah tersedia.'
                            : self::PESAN_LOLOS_OFFERING_LETTER,
                        'saran' => null,
                        'jadwal_offering_letter' => $jadwalOfferingLetterData,
                        'jadwalOfferingLetter' => $jadwalOfferingLetterData,
                    ];
                }
            }
        }

        $status = 'Administrasi';
        $keterangan = 'Status seleksi kandidat saat ini berada pada tahap Administrasi.';
        $saran = 'Silakan pantau halaman ini secara berkala untuk melihat perkembangan proses seleksi.';
        $tahapanTerakhir = 'Administrasi';

        if ($punyaJadwalTest) {
            if ($bolehLanjutJadwalTestZoom) {
                $status = 'Jadwal Test Zoom Tersedia';
                $keterangan = 'Kandidat sudah mendapatkan jadwal test Zoom.';
                $saran = 'Silakan mengikuti test Zoom sesuai jadwal yang sudah ditentukan.';
                $tahapanTerakhir = 'Jadwal Test Zoom';
            } else {
                $status = 'Pendaftaran Belum Lengkap';
                $keterangan = 'Jadwal test Zoom belum dapat dilanjutkan karena data pendaftaran belum lengkap sampai tahap Kesiapan Bekerja.';
                $saran = $pesanBelumLengkap;
                $tahapanTerakhir = $tahapFormTerakhir;
            }
        }

        if ($punyaHasilTest) {
            $status = $hasilTest === 'lolos' ? 'Lolos Seleksi Test Zoom' : 'Gagal Seleksi Test Zoom';
            $keterangan = $hasilTest === 'lolos'
                ? 'Kandidat dinyatakan lolos pada seleksi test Zoom.'
                : 'Kandidat dinyatakan belum lolos pada seleksi test Zoom.';
            $saran = $hasilTest === 'lolos'
                ? self::PESAN_LOLOS_SELEKSI
                : self::PESAN_TIDAK_LOLOS_SELEKSI;
            $tahapanTerakhir = 'Hasil Seleksi Test Zoom';
        }

        if ($punyaJadwalTestMmpi) {
            $status = 'Jadwal Test MMPI Tersedia';
            $keterangan = 'Kandidat sudah mendapatkan jadwal test MMPI.';
            $saran = 'Silakan mengikuti test MMPI sesuai jadwal yang sudah ditentukan.';
            $tahapanTerakhir = 'Jadwal Test MMPI';
        }

        if ($punyaHasilTestMmpi) {
            $status = $hasilTestMmpi === 'lolos' ? 'Lolos Seleksi Test MMPI' : 'Gagal Seleksi Test MMPI';
            $keterangan = $hasilTestMmpi === 'lolos'
                ? 'Kandidat dinyatakan lolos pada seleksi test MMPI.'
                : 'Kandidat dinyatakan belum lolos pada seleksi test MMPI.';
            $saran = $hasilTestMmpi === 'lolos'
                ? self::PESAN_LOLOS_SELEKSI
                : self::PESAN_TIDAK_LOLOS_SELEKSI;
            $tahapanTerakhir = 'Hasil Seleksi Test MMPI';
        }

        if ($punyaJadwalInterview) {
            if ($isInterviewReschedule) {
                $status = 'Interview Reschedule';
                $keterangan = 'Jadwal interview kandidat sedang dalam proses penjadwalan ulang.';
                $saran = 'Silakan pantau informasi jadwal interview terbaru dari tim rekrutmen.';
                $tahapanTerakhir = 'Reschedule Interview';
            } else {
                $status = 'Jadwal Interview Tersedia';
                $keterangan = 'Kandidat sudah mendapatkan jadwal interview.';
                $saran = 'Silakan mengikuti interview sesuai jadwal yang sudah ditentukan.';
                $tahapanTerakhir = 'Jadwal Interview';
            }
        }

        if ($punyaHasilInterview) {
            if ($hasilInterviewGagal) {
                $status = 'Gagal Interview';
                $keterangan = 'Kandidat dinyatakan tidak lolos pada tahap interview.';
                $saran = self::PESAN_TIDAK_LOLOS_SELEKSI;
                $tahapanTerakhir = 'Interview';
            } elseif ($hasilInterviewLanjutReview) {
                if ($reviewGagal) {
                    $status = 'Gagal Interview';
                    $keterangan = 'Kandidat dinyatakan gagal berdasarkan hasil Review Management.';
                    $saran = self::PESAN_TIDAK_LOLOS_SELEKSI;
                    $tahapanTerakhir = 'Interview';
                } elseif ($reviewDiterima) {
                    if ($punyaJadwalOfferingLetter) {
                        $status = 'Jadwal Offering Letter';
                        $keterangan = $this->normalizeStatusOfferingLetterValue($statusJadwalOfferingLetter) === 'Menerima'
                            ? 'Kandidat sudah menerima Offering Letter dan siap untuk bekerja.'
                            : 'Jadwal Offering Letter sudah tersedia.';
                        $saran = $this->getSaranOfferingLetter($statusJadwalOfferingLetter);
                    } else {
                        $status = 'Jadwal Offering Letter Pending';
                        $keterangan = 'Kandidat sudah lolos interview dan sedang menunggu jadwal Offering Letter.';
                        $saran = self::PESAN_LOLOS_OFFERING_LETTER;
                    }
                    $tahapanTerakhir = 'Jadwal Offering Letter';
                } else {
                    $status = 'Review Management';
                    $keterangan = 'Kandidat sudah memiliki hasil interview dan sedang menunggu hasil Review Management.';
                    $saran = 'Silakan pantau halaman ini secara berkala untuk melihat hasil Review Management.';
                    $tahapanTerakhir = 'Review Management';
                }
            }
        }

        $jadwalTestPayload = $jadwalTestData ? array_merge($jadwalTestData, [
            'boleh_akses_jadwal_test_zoom' => $bolehLanjutJadwalTestZoom,
            'bolehAksesJadwalTestZoom' => $bolehLanjutJadwalTestZoom,
            'disabled' => !$bolehLanjutJadwalTestZoom,
            'disabled_reason' => $bolehLanjutJadwalTestZoom ? null : $pesanBelumLengkap,
            'disabledReason' => $bolehLanjutJadwalTestZoom ? null : $pesanBelumLengkap,
        ]) : null;

        return [
            'status' => $status,
            'keterangan' => $keterangan,
            'saran' => $saran,
            'tahapan_terakhir' => $tahapanTerakhir,

            'kelengkapan_form' => $formCompletion,
            'persentase_kelengkapan' => $formCompletion['percentage'],
            'total_step_terisi' => $formCompletion['completed_steps'],
            'total_step_form' => $formCompletion['total_steps'],
            'tahap_terakhir_form' => $formCompletion['last_completed_label'],

            'boleh_melanjutkan_jadwal_test_zoom' => $bolehLanjutJadwalTestZoom,
            'bolehMelanjutkanJadwalTestZoom' => $bolehLanjutJadwalTestZoom,
            'can_access_jadwal_test_zoom' => $bolehLanjutJadwalTestZoom,
            'canAccessJadwalTestZoom' => $bolehLanjutJadwalTestZoom,
            'pesan_jadwal_test_zoom' => $bolehLanjutJadwalTestZoom ? null : $pesanBelumLengkap,
            'pesanJadwalTestZoom' => $bolehLanjutJadwalTestZoom ? null : $pesanBelumLengkap,

            'id' => $pelamar->id,
            'token' => $pelamar->token,
            'nama_pelamar' => $pelamar->nama_lengkap ?? '-',
            'nama_lengkap' => $pelamar->nama_lengkap ?? '-',
            'posisi_dilamar' => $this->getLabelRelasi($pelamar->posisi, ['nama_posisi', 'posisi', 'nama_posisi_dilamar', 'posisi_dilamar', 'jabatan', 'nama_jabatan', 'nama'], $pelamar->posisi_yang_dilamar ?? null),
            'posisi_yang_dilamar' => $this->getLabelRelasi($pelamar->posisi, ['nama_posisi', 'posisi', 'nama_posisi_dilamar', 'posisi_dilamar', 'jabatan', 'nama_jabatan', 'nama'], $pelamar->posisi_yang_dilamar ?? null),
            'perusahaan_dilamar' => $this->getLabelRelasi($pelamar->perusahaan, ['nama_perusahaan', 'perusahaan', 'nama'], $pelamar->perusahaan_dilamar ?? null),

            'hasil_test' => $hasilTest,
            'hasilTest' => $hasilTest,
            'hasil_test_zoom' => $hasilTest,
            'hasilTestZoom' => $hasilTest,
            'hasil_test_mmpi' => $hasilTestMmpi,
            'hasilTestMmpi' => $hasilTestMmpi,
            'status_hasil_test_mmpi' => $hasilTestMmpi,
            'statusHasilTestMmpi' => $hasilTestMmpi,

            'jadwal_interview' => $jadwalInterviewData,
            'jadwalInterview' => $jadwalInterviewData,
            'interview' => $jadwalInterviewData,
            'hasil_interview' => $hasilInterview,
            'hasilInterview' => $hasilInterview,
            'status_hasil_interview' => $hasilInterview,
            'statusHasilInterview' => $hasilInterview,
            'status_kehadiran_interview' => $jadwalInterviewData['kehadiran'] ?? null,
            'statusKehadiranInterview' => $jadwalInterviewData['kehadiran'] ?? null,
            'catatan_interview' => $jadwalInterviewData['catatan'] ?? null,
            'catatanInterview' => $jadwalInterviewData['catatan'] ?? null,

            'review_management_id' => $jadwalInterviewData['review_management_id'] ?? null,
            'reviewManagementId' => $jadwalInterviewData['review_management_id'] ?? null,
            'hasil_review_management_id' => $jadwalInterviewData['hasil_review_management_id'] ?? null,
            'hasilReviewManagementId' => $jadwalInterviewData['hasil_review_management_id'] ?? null,
            'review_management' => $reviewManagement,
            'reviewManagement' => $reviewManagement,
            'status_review_management' => $statusReviewManagement,
            'statusReviewManagement' => $statusReviewManagement,
            'status_review' => $statusReviewManagement,
            'statusReview' => $statusReviewManagement,

            'jadwal_offering_letter' => $jadwalOfferingLetterData,
            'jadwalOfferingLetter' => $jadwalOfferingLetterData,
            'status_jadwal_offering_letter' => $statusJadwalOfferingLetter,
            'statusJadwalOfferingLetter' => $statusJadwalOfferingLetter,

            'jadwal_test_mmpi' => $jadwalTestMmpiData,
            'jadwalTestMmpi' => $jadwalTestMmpiData,
            'jadwal_mmpi' => $jadwalTestMmpiData,
            'jadwalMmpi' => $jadwalTestMmpiData,
            'jadwal_test' => $jadwalTestPayload,
            'jadwal_test_zoom' => $jadwalTestPayload,
            'jadwalTestZoom' => $jadwalTestPayload,

            'tahapan' => $tahapan,
            'tahapan_seleksi' => [
                'jadwal_test' => $jadwalTestData,
                'jadwal_test_zoom' => $jadwalTestData,
                'jadwal_test_mmpi' => $jadwalTestMmpiData,
                'jadwal_interview' => $jadwalInterviewData,
                'jadwal_offering_letter' => $jadwalOfferingLetterData,
                'hasil_test' => $hasilTest,
                'hasil_test_zoom' => $hasilTest,
                'hasil_test_mmpi' => $hasilTestMmpi,
                'hasil_interview' => $hasilInterview,
                'review_management' => $reviewManagement,
                'status_review_management' => $statusReviewManagement,
                'boleh_melanjutkan_jadwal_test_zoom' => $bolehLanjutJadwalTestZoom,
                'pesan_jadwal_test_zoom' => $bolehLanjutJadwalTestZoom ? null : $pesanBelumLengkap,
                'tahapan' => $tahapan,
            ],
            'tahapanSeleksi' => [
                'jadwal_test' => $jadwalTestData,
                'jadwal_test_zoom' => $jadwalTestData,
                'jadwal_test_mmpi' => $jadwalTestMmpiData,
                'jadwal_interview' => $jadwalInterviewData,
                'jadwal_offering_letter' => $jadwalOfferingLetterData,
                'hasil_test' => $hasilTest,
                'hasil_test_zoom' => $hasilTest,
                'hasil_test_mmpi' => $hasilTestMmpi,
                'hasil_interview' => $hasilInterview,
                'reviewManagement' => $reviewManagement,
                'statusReviewManagement' => $statusReviewManagement,
                'bolehMelanjutkanJadwalTestZoom' => $bolehLanjutJadwalTestZoom,
                'pesanJadwalTestZoom' => $bolehLanjutJadwalTestZoom ? null : $pesanBelumLengkap,
                'tahapan' => $tahapan,
            ],
        ];
    }

    private function canAccessJadwalTestZoom(DataRiwayatDiri $pelamar): bool
    {
        $completion = $this->calculateFormCompletion($pelamar);

        return (int) ($completion['completed_steps'] ?? 0) >= (int) ($completion['total_steps'] ?? 5);
    }

    private function calculateFormCompletion(DataRiwayatDiri $pelamar): array
    {
        $pelamar->loadMissing([
            'riwayatKeluarga',
            'saudaraKandung',
            'saudaraIpar',
            'riwayatKesehatan',
            'riwayatPekerjaan',
            'kesiapanBekerja',
        ]);

        $steps = [
            [
                'key' => 'data_diri',
                'label' => 'Data Diri',
                'order' => 1,
                'completed' => $this->hasFilledValue($pelamar, [
                    'nama_lengkap',
                    'nama_panggil',
                    'email',
                    'no_wa',
                    'pendidikan_id',
                    'jurusan',
                    'nama_institusi',
                    'agama_id',
                    'tanggal_lahir',
                    'tempat_lahir',
                    'jenis_kelamin',
                    'alamat_ktp',
                    'alamat_domisili',
                    'posisi_yang_dilamar',
                    'posisi_dilamar',
                    'perusahaan_dilamar',
                    'kewarganegaraan_id',
                    'status_pernikahan_id',
                ]),
            ],
            [
                'key' => 'riwayat_keluarga',
                'label' => 'Riwayat Keluarga',
                'order' => 2,
                'completed' =>
                    $this->hasFilledValue($pelamar->riwayatKeluarga ?? null, [
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
                        'tlpn_suami_istri',
                        'nama_bapak_mertua',
                        'pekerjaan_bapak_mertua',
                        'nama_ibu_mertua',
                        'pekerjaan_ibu_mertua',
                        'hubungan_kerabat_instansi',
                        'kerabat_bekerja_diinstansi',
                        'kontak_darurat',
                        'tlpn_darurat',
                    ]) ||
                    $this->collectionHasFilledValue($pelamar->saudaraKandung ?? null, [
                        'nama_saudara_kandung',
                        'jenis_kelamin',
                        'hubungan',
                        'pekerjaan',
                        'no_hp',
                        'alamat',
                    ]) ||
                    $this->collectionHasFilledValue($pelamar->saudaraIpar ?? null, [
                        'nama_saudara_ipar',
                        'jenis_kelamin',
                        'hubungan',
                        'pekerjaan',
                        'no_hp',
                        'alamat',
                    ]),
            ],
            [
                'key' => 'riwayat_kesehatan',
                'label' => 'Riwayat Kesehatan',
                'order' => 3,
                'completed' =>
                    $this->hasFilledValue($pelamar->riwayatKesehatan ?? null, [
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
                    ]) ||
                    $this->hasFilledValue($pelamar, [
                        'gol_darah',
                        'tinggi_badan',
                        'berat_badan',
                    ]),
            ],
            [
                'key' => 'riwayat_pekerjaan',
                'label' => 'Riwayat Pekerjaan',
                'order' => 4,
                'completed' => $this->hasFilledValue($pelamar->riwayatPekerjaan ?? null, [
                    'nama_perusahaan',
                    'posisi_pekerjaan_terakhir',
                    'periode_kerja_awal',
                    'periode_kerja_akhir',
                    'gaji_terakhir',
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
                    'refrensi_kerja',
                    'nama_refrensi',
                    'telp_refrensi',
                    'refrensi_rekan_kerja',
                    'nama_refrensi_rekan',
                    'telp_refrensi_rekan',
                    'refrensi_kerabat',
                    'nama_refrensi_kerabat',
                    'telp_refrensi_kerabat',
                ]),
            ],
            [
                'key' => 'kesiapan_bekerja',
                'label' => 'Kesiapan Bekerja',
                'order' => 5,
                'completed' => $this->hasFilledValue($pelamar->kesiapanBekerja ?? null, [
                    'kapan_siap_bekerja',
                    'ekpetasi_gaji',
                    'gaji_diharapkan',
                    'penempatan',
                    'proses_bkhang',
                    'proses_bhaking',
                    'background_checking',
                    'dapat_dipertanggung_jawabkan',
                    'pernyataan_data_benar',
                    'bersedia_training',
                    'bersedia_pelatihan',
                ]),
            ],
        ];

        $highestCompletedOrder = 0;
        $lastCompletedLabel = '-';

        foreach ($steps as $step) {
            if (!empty($step['completed']) && (int) $step['order'] > $highestCompletedOrder) {
                $highestCompletedOrder = (int) $step['order'];
                $lastCompletedLabel = $step['label'];
            }
        }

        $totalSteps = count($steps);

        $steps = collect($steps)
            ->map(function ($step) use ($highestCompletedOrder) {
                $step['completed'] = (int) $step['order'] <= $highestCompletedOrder;
                $step['percentage'] = round(((int) $step['order'] / 5) * 100);
                return $step;
            })
            ->values()
            ->all();

        return [
            'percentage' => $totalSteps > 0 ? round(($highestCompletedOrder / $totalSteps) * 100) : 0,
            'completed_steps' => $highestCompletedOrder,
            'total_steps' => $totalSteps,
            'last_completed_label' => $lastCompletedLabel,
            'steps' => $steps,
        ];
    }

    private function collectionHasFilledValue($collection, array $fields): bool
    {
        if (!$collection) {
            return false;
        }

        foreach ($collection as $row) {
            if ($this->hasFilledValue($row, $fields)) {
                return true;
            }
        }

        return false;
    }

    private function hasFilledValue($target, array $fields): bool
    {
        if (!$target) {
            return false;
        }

        foreach ($fields as $field) {
            if (is_array($target)) {
                $value = $target[$field] ?? null;
            } elseif (is_object($target) && method_exists($target, 'getAttribute')) {
                $value = $target->getAttribute($field);
            } elseif (is_object($target)) {
                $value = $target->{$field} ?? null;
            } else {
                $value = null;
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

            if (in_array(strtolower($trimmed), ['[]', '{}', 'null'], true)) {
                return false;
            }

            return true;
        }

        if (is_array($value)) {
            return collect($value)->contains(function ($item) {
                if (is_array($item)) {
                    return collect($item)->contains(fn ($child) => $this->isFilledValue($child));
                }

                return $this->isFilledValue($item);
            });
        }

        if (is_bool($value)) {
            return true;
        }

        if (is_numeric($value)) {
            return true;
        }

        return !empty($value);
    }


    private function formatJadwalInterview(?DataRiwayatDiri $pelamar): ?array
    {
        if (!$pelamar || !Schema::hasTable('jadwal_interview') || !Schema::hasTable('jadwal_interview_kandidat')) {
            return null;
        }

        $hasReviewTable = Schema::hasTable('hasil_review_management');
        $hasOlTable = Schema::hasTable('jadwal_offering_letters');

        $query = DB::table('jadwal_interview_kandidat as jik')
            ->join('jadwal_interview as ji', 'ji.id', '=', 'jik.jadwal_interview_id')
            ->where('jik.data_riwayat_diri_id', $pelamar->id);

        if ($hasReviewTable) {
            $query->leftJoin('hasil_review_management as hrm', function ($join) {
                $join->on('hrm.hasil_interview_id', '=', 'jik.id');

                if (Schema::hasColumn('hasil_review_management', 'deleted_at')) {
                    $join->whereNull('hrm.deleted_at');
                }
            });
        }

        if ($hasOlTable && $hasReviewTable) {
            $query->leftJoin('jadwal_offering_letters as jol', function ($join) {
                $join->on('jol.hasil_review_management_id', '=', 'hrm.id');

                if (Schema::hasColumn('jadwal_offering_letters', 'deleted_at')) {
                    $join->whereNull('jol.deleted_at');
                }
            });
        }

        if (Schema::hasColumn('jadwal_interview_kandidat', 'deleted_at')) {
            $query->whereNull('jik.deleted_at');
        }

        if (Schema::hasColumn('jadwal_interview', 'deleted_at')) {
            $query->whereNull('ji.deleted_at');
        }

        $select = [
            'jik.id as pivot_id',
            'jik.jadwal_interview_id',
            'jik.data_riwayat_diri_id',
            'ji.id as id',
            'ji.judul_interview',
            'ji.jadwal_interview',
            DB::raw(Schema::hasColumn('jadwal_interview_kandidat', 'created_at') ? 'jik.created_at as interview_created_at' : 'NULL as interview_created_at'),
            DB::raw(Schema::hasColumn('jadwal_interview_kandidat', 'updated_at') ? 'jik.updated_at as interview_updated_at' : 'NULL as interview_updated_at'),
        ];

        foreach (['status_kehadiran', 'hasil_interview', 'catatan'] as $column) {
            $select[] = Schema::hasColumn('jadwal_interview_kandidat', $column)
                ? DB::raw("jik.{$column} as {$column}")
                : DB::raw("NULL as {$column}");
        }

        if ($hasReviewTable) {
            $select[] = DB::raw('hrm.id as review_management_id');
            $select[] = DB::raw('hrm.id as hasil_review_management_id');
            $select[] = DB::raw('hrm.hasil_interview_id as review_hasil_interview_id');
            $select[] = Schema::hasColumn('hasil_review_management', 'review_management')
                ? DB::raw('hrm.review_management as review_management')
                : DB::raw('NULL as review_management');
            $select[] = Schema::hasColumn('hasil_review_management', 'status')
                ? DB::raw('hrm.status as status_review_management')
                : DB::raw('NULL as status_review_management');
        } else {
            $select[] = DB::raw('NULL as review_management_id');
            $select[] = DB::raw('NULL as hasil_review_management_id');
            $select[] = DB::raw('NULL as review_hasil_interview_id');
            $select[] = DB::raw('NULL as review_management');
            $select[] = DB::raw('NULL as status_review_management');
        }

        if ($hasOlTable && $hasReviewTable) {
            $select[] = DB::raw('jol.id as jadwal_offering_letter_id');
            $select[] = Schema::hasColumn('jadwal_offering_letters', 'tanggal_ol') ? DB::raw('jol.tanggal_ol as tanggal_ol') : DB::raw('NULL as tanggal_ol');
            $select[] = Schema::hasColumn('jadwal_offering_letters', 'jam_ol') ? DB::raw('jol.jam_ol as jam_ol') : DB::raw('NULL as jam_ol');
            $select[] = Schema::hasColumn('jadwal_offering_letters', 'metode') ? DB::raw('jol.metode as metode_ol') : DB::raw('NULL as metode_ol');
            $select[] = Schema::hasColumn('jadwal_offering_letters', 'link') ? DB::raw('jol.link as link_ol') : DB::raw('NULL as link_ol');
            $select[] = Schema::hasColumn('jadwal_offering_letters', 'pic') ? DB::raw('jol.pic as pic_ol') : DB::raw('NULL as pic_ol');
            $select[] = Schema::hasColumn('jadwal_offering_letters', 'catatan') ? DB::raw('jol.catatan as catatan_ol') : DB::raw('NULL as catatan_ol');
            $select[] = Schema::hasColumn('jadwal_offering_letters', 'status_jadwal') ? DB::raw('jol.status_jadwal as status_jadwal_ol') : DB::raw('NULL as status_jadwal_ol');
            $select[] = Schema::hasColumn('jadwal_offering_letters', 'created_at') ? DB::raw('jol.created_at as jadwal_ol_created_at') : DB::raw('NULL as jadwal_ol_created_at');
            $select[] = Schema::hasColumn('jadwal_offering_letters', 'updated_at') ? DB::raw('jol.updated_at as jadwal_ol_updated_at') : DB::raw('NULL as jadwal_ol_updated_at');
        } else {
            $select[] = DB::raw('NULL as jadwal_offering_letter_id');
            $select[] = DB::raw('NULL as tanggal_ol');
            $select[] = DB::raw('NULL as jam_ol');
            $select[] = DB::raw('NULL as metode_ol');
            $select[] = DB::raw('NULL as link_ol');
            $select[] = DB::raw('NULL as pic_ol');
            $select[] = DB::raw('NULL as catatan_ol');
            $select[] = DB::raw('NULL as status_jadwal_ol');
            $select[] = DB::raw('NULL as jadwal_ol_created_at');
            $select[] = DB::raw('NULL as jadwal_ol_updated_at');
        }

        $jadwalInterview = $query
            ->select($select)
            ->orderByDesc('ji.jadwal_interview')
            ->orderByDesc('jik.created_at')
            ->first();

        if (!$jadwalInterview || empty($jadwalInterview->jadwal_interview)) {
            return null;
        }

        $jadwal = $this->parseDateTime($jadwalInterview->jadwal_interview);

        if (!$jadwal) {
            return null;
        }

        $kehadiran = $this->normalizeKehadiranInterviewValue($jadwalInterview->status_kehadiran ?? null);
        $hasilInterview = $this->normalizeHasilInterviewValue($jadwalInterview->hasil_interview ?? null);
        $statusReviewManagement = $this->normalizeStatusReviewManagementValue($jadwalInterview->status_review_management ?? null);
        $statusJadwalOfferingLetter = $this->normalizeStatusOfferingLetterValue($jadwalInterview->status_jadwal_ol ?? null);
        $catatan = $jadwalInterview->catatan ?? null;

        $jadwalOfferingLetter = null;

        if (!empty($jadwalInterview->jadwal_offering_letter_id)) {
            $tanggalOl = $this->parseDateTime($jadwalInterview->tanggal_ol ?? null);

            $jadwalOfferingLetter = [
                'id' => $jadwalInterview->jadwal_offering_letter_id,
                'hasil_review_management_id' => $jadwalInterview->hasil_review_management_id ?? null,
                'hasilReviewManagementId' => $jadwalInterview->hasil_review_management_id ?? null,
                'tanggal_ol' => $jadwalInterview->tanggal_ol ?? null,
                'tanggalOl' => $jadwalInterview->tanggal_ol ?? null,
                'tanggal_label' => $tanggalOl ? $tanggalOl->translatedFormat('d F Y') : null,
                'tanggalLabel' => $tanggalOl ? $tanggalOl->translatedFormat('d F Y') : null,
                'jam_ol' => $jadwalInterview->jam_ol ?? null,
                'jamOl' => $jadwalInterview->jam_ol ?? null,
                'metode' => $jadwalInterview->metode_ol ?? null,
                'link' => $jadwalInterview->link_ol ?? null,
                'pic' => $jadwalInterview->pic_ol ?? null,
                'catatan' => $jadwalInterview->catatan_ol ?? null,
                'status_jadwal' => $statusJadwalOfferingLetter ?: 'Pending',
                'statusJadwal' => $statusJadwalOfferingLetter ?: 'Pending',
                'status_jadwal_raw' => $jadwalInterview->status_jadwal_ol ?? null,
                'statusJadwalRaw' => $jadwalInterview->status_jadwal_ol ?? null,
                'created_at' => $jadwalInterview->jadwal_ol_created_at ?? null,
                'updated_at' => $jadwalInterview->jadwal_ol_updated_at ?? null,
            ];
        }

        return [
            'id' => $jadwalInterview->id ?? null,
            'pivot_id' => $jadwalInterview->pivot_id ?? null,
            'pivotId' => $jadwalInterview->pivot_id ?? null,
            'jadwal_interview_kandidat_id' => $jadwalInterview->pivot_id ?? null,
            'jadwalInterviewKandidatId' => $jadwalInterview->pivot_id ?? null,
            'jadwal_interview_id' => $jadwalInterview->jadwal_interview_id ?? null,
            'jadwalInterviewId' => $jadwalInterview->jadwal_interview_id ?? null,
            'data_riwayat_diri_id' => $jadwalInterview->data_riwayat_diri_id ?? null,
            'dataRiwayatDiriId' => $jadwalInterview->data_riwayat_diri_id ?? null,
            'judul_interview' => $jadwalInterview->judul_interview ?? 'Interview',
            'judulInterview' => $jadwalInterview->judul_interview ?? 'Interview',
            'nama_interview' => $jadwalInterview->judul_interview ?? 'Interview',
            'namaInterview' => $jadwalInterview->judul_interview ?? 'Interview',
            'jadwal' => $jadwal->toDateTimeString(),
            'jadwal_interview' => $jadwal->toDateTimeString(),
            'jadwalInterview' => $jadwal->toDateTimeString(),
            'tanggal' => $jadwal->translatedFormat('d F Y'),
            'jam' => $jadwal->format('H:i'),
            'kehadiran' => $kehadiran,
            'status_kehadiran' => $kehadiran,
            'statusKehadiran' => $kehadiran,
            'status_kehadiran_interview' => $kehadiran,
            'statusKehadiranInterview' => $kehadiran,
            'konfirmasi_kehadiran' => $kehadiran,
            'konfirmasiKehadiran' => $kehadiran,
            'sudah_mengisi_kehadiran' => !empty($kehadiran),
            'sudahMengisiKehadiran' => !empty($kehadiran),
            'hasil_interview_raw' => $jadwalInterview->hasil_interview ?? null,
            'hasilInterviewRaw' => $jadwalInterview->hasil_interview ?? null,
            'hasil_interview_label' => $jadwalInterview->hasil_interview ?? null,
            'hasilInterviewLabel' => $jadwalInterview->hasil_interview ?? null,
            'hasil_interview' => $hasilInterview,
            'hasilInterview' => $hasilInterview,
            'status_hasil_interview' => $hasilInterview,
            'statusHasilInterview' => $hasilInterview,
            'sudah_ada_hasil_interview' => !empty($hasilInterview),
            'sudahAdaHasilInterview' => !empty($hasilInterview),
            'catatan' => $catatan,
            'catatan_interview' => $catatan,
            'catatanInterview' => $catatan,
            'review_management_id' => $jadwalInterview->review_management_id ?? null,
            'reviewManagementId' => $jadwalInterview->review_management_id ?? null,
            'hasil_review_management_id' => $jadwalInterview->hasil_review_management_id ?? null,
            'hasilReviewManagementId' => $jadwalInterview->hasil_review_management_id ?? null,
            'review_hasil_interview_id' => $jadwalInterview->review_hasil_interview_id ?? null,
            'reviewHasilInterviewId' => $jadwalInterview->review_hasil_interview_id ?? null,
            'review_management' => $jadwalInterview->review_management ?? null,
            'reviewManagement' => $jadwalInterview->review_management ?? null,
            'status_review_management' => $statusReviewManagement,
            'statusReviewManagement' => $statusReviewManagement,
            'status_review' => $statusReviewManagement,
            'statusReview' => $statusReviewManagement,
            'jadwal_offering_letter' => $jadwalOfferingLetter,
            'jadwalOfferingLetter' => $jadwalOfferingLetter,
            'status_jadwal_offering_letter' => $statusJadwalOfferingLetter ?: ($jadwalOfferingLetter ? 'Pending' : null),
            'statusJadwalOfferingLetter' => $statusJadwalOfferingLetter ?: ($jadwalOfferingLetter ? 'Pending' : null),
            'created_at' => $jadwalInterview->interview_created_at ?? null,
            'updated_at' => $jadwalInterview->interview_updated_at ?? null,
        ];
    }

    private function formatJadwalTestMmpi(?DataRiwayatDiri $pelamar): ?array
    {
        if (!$pelamar || !Schema::hasTable('jadwal_test_mmpi')) {
            return null;
        }

        $query = DB::table('jadwal_test_mmpi as jtm')
            ->where('jtm.data_riwayat_diri_id', $pelamar->id);

        if (Schema::hasColumn('jadwal_test_mmpi', 'deleted_at')) {
            $query->whereNull('jtm.deleted_at');
        }

        if (Schema::hasTable('daftar_hadir_test_mmpi')) {
            $query->leftJoin('daftar_hadir_test_mmpi as dhm', function ($join) {
                $join->on('dhm.jadwal_test_mmpi_id', '=', 'jtm.id');

                if (Schema::hasColumn('daftar_hadir_test_mmpi', 'deleted_at')) {
                    $join->whereNull('dhm.deleted_at');
                }
            });
        }

        if (Schema::hasColumn('jadwal_test_mmpi', 'daftar_hadir_test_zoom_id') && Schema::hasTable('daftar_hadir_test_zoom')) {
            $query->leftJoin('daftar_hadir_test_zoom as dh', function ($join) {
                $join->on('dh.id', '=', 'jtm.daftar_hadir_test_zoom_id');

                if (Schema::hasColumn('daftar_hadir_test_zoom', 'deleted_at')) {
                    $join->whereNull('dh.deleted_at');
                }
            });
        }

        $jadwalMmpi = $query
            ->select([
                'jtm.id',
                DB::raw(Schema::hasColumn('jadwal_test_mmpi', 'daftar_hadir_test_zoom_id') ? 'jtm.daftar_hadir_test_zoom_id' : 'NULL as daftar_hadir_test_zoom_id'),
                'jtm.data_riwayat_diri_id',
                'jtm.tanggal',
                DB::raw(Schema::hasTable('daftar_hadir_test_mmpi') ? 'dhm.id as daftar_hadir_test_mmpi_id' : 'NULL as daftar_hadir_test_mmpi_id'),
                DB::raw(Schema::hasTable('daftar_hadir_test_mmpi') ? 'dhm.tanggal_kehadiran as tanggal_kehadiran_mmpi' : 'NULL as tanggal_kehadiran_mmpi'),
                DB::raw(Schema::hasTable('daftar_hadir_test_mmpi') ? 'dhm.status_kehadiran as status_kehadiran_mmpi' : 'NULL as status_kehadiran_mmpi'),
                DB::raw(Schema::hasTable('daftar_hadir_test_mmpi') ? 'dhm.hasil_test as hasil_test_mmpi' : 'NULL as hasil_test_mmpi'),
                DB::raw(Schema::hasTable('daftar_hadir_test_zoom') ? 'dh.status_kehadiran as status_kehadiran_zoom' : 'NULL as status_kehadiran_zoom'),
                DB::raw(Schema::hasTable('daftar_hadir_test_zoom') ? 'dh.hasil_test as hasil_test_zoom' : 'NULL as hasil_test_zoom'),
            ])
            ->orderByDesc('jtm.tanggal')
            ->orderByDesc('jtm.created_at')
            ->first();

        if (!$jadwalMmpi || empty($jadwalMmpi->tanggal)) {
            return null;
        }

        $tanggal = $this->parseDateTime($jadwalMmpi->tanggal);

        if (!$tanggal) {
            return null;
        }

        $kehadiranMmpi = $this->normalizeKehadiranValue($jadwalMmpi->status_kehadiran_mmpi ?? null);
        $hasilTestMmpi = $this->normalizeHasilTestValue($jadwalMmpi->hasil_test_mmpi ?? null);

        return [
            'id' => $jadwalMmpi->id,
            'daftar_hadir_test_mmpi_id' => $jadwalMmpi->daftar_hadir_test_mmpi_id ?? null,
            'daftarHadirTestMmpiId' => $jadwalMmpi->daftar_hadir_test_mmpi_id ?? null,
            'daftar_hadir_test_zoom_id' => $jadwalMmpi->daftar_hadir_test_zoom_id ?? null,
            'daftarHadirTestZoomId' => $jadwalMmpi->daftar_hadir_test_zoom_id ?? null,
            'data_riwayat_diri_id' => $jadwalMmpi->data_riwayat_diri_id,
            'dataRiwayatDiriId' => $jadwalMmpi->data_riwayat_diri_id,

            'jadwal' => $tanggal->toDateString(),
            'tanggal' => $tanggal->toDateString(),
            'tanggal_label' => $tanggal->translatedFormat('d F Y'),
            'tanggalLabel' => $tanggal->translatedFormat('d F Y'),
            'jam' => $tanggal->format('H:i'),

            'tanggal_kehadiran' => $jadwalMmpi->tanggal_kehadiran_mmpi ?? null,
            'tanggalKehadiran' => $jadwalMmpi->tanggal_kehadiran_mmpi ?? null,

            'kehadiran' => $kehadiranMmpi,
            'status_kehadiran' => $kehadiranMmpi,
            'statusKehadiran' => $kehadiranMmpi,
            'konfirmasi_kehadiran' => $kehadiranMmpi,
            'konfirmasiKehadiran' => $kehadiranMmpi,
            'sudah_mengisi_kehadiran' => !empty($kehadiranMmpi),
            'sudahMengisiKehadiran' => !empty($kehadiranMmpi),

            'hasil_test' => $hasilTestMmpi,
            'hasilTest' => $hasilTestMmpi,
            'status_hasil_test' => $hasilTestMmpi,
            'statusHasilTest' => $hasilTestMmpi,

            'status_kehadiran_zoom' => $this->normalizeKehadiranValue($jadwalMmpi->status_kehadiran_zoom ?? null),
            'statusKehadiranZoom' => $this->normalizeKehadiranValue($jadwalMmpi->status_kehadiran_zoom ?? null),
            'hasil_test_zoom' => $this->normalizeHasilTestValue($jadwalMmpi->hasil_test_zoom ?? null),
            'hasilTestZoom' => $this->normalizeHasilTestValue($jadwalMmpi->hasil_test_zoom ?? null),
        ];
    }

    private function formatJadwalTest($jadwalTest): ?array
    {
        if (!$jadwalTest || empty($jadwalTest->jadwal)) {
            return null;
        }

        $jadwal = $this->parseDateTime($jadwalTest->jadwal);

        if (!$jadwal) {
            return null;
        }

        $table = $jadwalTest->getTable();
        $daftarHadir = $this->getDaftarHadirByJadwal($jadwalTest);

        $kehadiran = $this->normalizeKehadiranValue(
            $daftarHadir->status_kehadiran ?? null
        );

        $hasilTest = $this->normalizeHasilTestValue(
            $daftarHadir->hasil_test ?? null
        );

        $linkZoom = null;

        if (Schema::hasColumn($table, 'link_zoom')) {
            $linkZoom = $jadwalTest->link_zoom ?: null;
        }

        $bolehBukaLinkZoom = $kehadiran === 'hadir' && !empty($linkZoom);

        return [
            'id' => $jadwalTest->id ?? null,

            'jadwal' => $jadwal->toDateTimeString(),
            'tanggal' => $jadwal->translatedFormat('d F Y'),
            'jam' => $jadwal->format('H:i'),

            'kehadiran' => $kehadiran,
            'status_kehadiran' => $kehadiran,
            'statusKehadiran' => $kehadiran,
            'konfirmasi_kehadiran' => $kehadiran,
            'konfirmasiKehadiran' => $kehadiran,

            'hasil_test' => $hasilTest,
            'hasilTest' => $hasilTest,
            'status_hasil_test' => $hasilTest,
            'statusHasilTest' => $hasilTest,

            'sudah_mengisi_kehadiran' => !empty($kehadiran),
            'sudahMengisiKehadiran' => !empty($kehadiran),

            'sudah_ada_hasil_test' => !empty($hasilTest),
            'sudahAdaHasilTest' => !empty($hasilTest),

            'link_zoom' => $bolehBukaLinkZoom ? $linkZoom : null,
            'linkZoom' => $bolehBukaLinkZoom ? $linkZoom : null,
            'zoom_link' => $bolehBukaLinkZoom ? $linkZoom : null,
            'zoomLink' => $bolehBukaLinkZoom ? $linkZoom : null,
            'url_link' => $bolehBukaLinkZoom ? $linkZoom : null,
            'urlLink' => $bolehBukaLinkZoom ? $linkZoom : null,

            'boleh_buka_link_zoom' => $bolehBukaLinkZoom,
            'bolehBukaLinkZoom' => $bolehBukaLinkZoom,
            'can_open_zoom_link' => $bolehBukaLinkZoom,
            'canOpenZoomLink' => $bolehBukaLinkZoom,
        ];
    }


    private function getDaftarHadirByJadwalMmpi($jadwalMmpi)
    {
        if (!$jadwalMmpi || !Schema::hasTable('daftar_hadir_test_mmpi')) {
            return null;
        }

        $query = DB::table('daftar_hadir_test_mmpi');

        if (Schema::hasColumn('daftar_hadir_test_mmpi', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        if (Schema::hasColumn('daftar_hadir_test_mmpi', 'jadwal_test_mmpi_id')) {
            $query->where('jadwal_test_mmpi_id', $jadwalMmpi->id);
        } else {
            $query->where('data_riwayat_diri_id', $jadwalMmpi->data_riwayat_diri_id);

            if (Schema::hasColumn('daftar_hadir_test_mmpi', 'tanggal_kehadiran') && !empty($jadwalMmpi->tanggal)) {
                $tanggal = $this->parseDateTime($jadwalMmpi->tanggal);

                if ($tanggal) {
                    $query->whereDate('tanggal_kehadiran', $tanggal->toDateString());
                }
            }
        }

        return $query->orderByDesc('created_at')->first();
    }

    private function saveDaftarHadirTestMmpi($jadwalMmpi, string $kehadiran): void
    {
        if (!Schema::hasTable('daftar_hadir_test_mmpi')) {
            throw new \RuntimeException('Tabel daftar_hadir_test_mmpi tidak ditemukan.');
        }

        $jadwal = $this->parseDateTime($jadwalMmpi->tanggal ?? null);
        $tanggalKehadiran = $jadwal ? $jadwal->toDateString() : now()->toDateString();
        $now = now();

        $existing = $this->getDaftarHadirByJadwalMmpi($jadwalMmpi);

        $data = [
            'data_riwayat_diri_id' => $jadwalMmpi->data_riwayat_diri_id,
            'status_kehadiran' => $kehadiran,
            'updated_at' => $now,
        ];

        if (Schema::hasColumn('daftar_hadir_test_mmpi', 'jadwal_test_mmpi_id')) {
            $data['jadwal_test_mmpi_id'] = $jadwalMmpi->id;
        }

        if (Schema::hasColumn('daftar_hadir_test_mmpi', 'tanggal_kehadiran')) {
            $data['tanggal_kehadiran'] = $tanggalKehadiran;
        }

        if ($existing) {
            DB::table('daftar_hadir_test_mmpi')
                ->where('id', $existing->id)
                ->update($data);

            return;
        }

        $data['id'] = (string) \Illuminate\Support\Str::uuid();
        $data['created_at'] = $now;

        DB::table('daftar_hadir_test_mmpi')->insert($data);
    }

    private function getDaftarHadirByJadwal($jadwalTest)
    {
        if (!$jadwalTest || !Schema::hasTable('daftar_hadir_test_zoom')) {
            return null;
        }

        $query = DB::table('daftar_hadir_test_zoom')
            ->whereNull('deleted_at');

        if (Schema::hasColumn('daftar_hadir_test_zoom', 'jadwal_test_zoom_id')) {
            $query->where('jadwal_test_zoom_id', $jadwalTest->id);
        } else {
            $query->where('data_riwayat_diri_id', $jadwalTest->data_riwayat_diri_id);

            if (Schema::hasColumn('daftar_hadir_test_zoom', 'tanggal_kehadiran') && !empty($jadwalTest->jadwal)) {
                $jadwal = $this->parseDateTime($jadwalTest->jadwal);

                if ($jadwal) {
                    $query->whereDate('tanggal_kehadiran', $jadwal->toDateString());
                }
            }
        }

        return $query->orderByDesc('created_at')->first();
    }

    private function saveDaftarHadirTestZoom(JadwalTestZoom $jadwalTest, string $kehadiran): void
    {
        if (!Schema::hasTable('daftar_hadir_test_zoom')) {
            throw new \RuntimeException('Tabel daftar_hadir_test_zoom tidak ditemukan.');
        }

        $jadwal = $this->parseDateTime($jadwalTest->jadwal);
        $tanggalKehadiran = $jadwal ? $jadwal->toDateString() : now()->toDateString();
        $now = now();

        $existing = $this->getDaftarHadirByJadwal($jadwalTest);

        $data = [
            'data_riwayat_diri_id' => $jadwalTest->data_riwayat_diri_id,
            'status_kehadiran' => $kehadiran,
            'updated_at' => $now,
        ];

        if (Schema::hasColumn('daftar_hadir_test_zoom', 'jadwal_test_zoom_id')) {
            $data['jadwal_test_zoom_id'] = $jadwalTest->id;
        }

        if (Schema::hasColumn('daftar_hadir_test_zoom', 'tanggal_kehadiran')) {
            $data['tanggal_kehadiran'] = $tanggalKehadiran;
        }

        if ($existing) {
            DB::table('daftar_hadir_test_zoom')
                ->where('id', $existing->id)
                ->update($data);

            return;
        }

        $data['id'] = (string) \Illuminate\Support\Str::uuid();
        $data['created_at'] = $now;

        DB::table('daftar_hadir_test_zoom')->insert($data);
    }

    private function parseDateTime($value): ?Carbon
    {
        if (!$value) {
            return null;
        }

        try {
            return $value instanceof Carbon
                ? $value->copy()
                : Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
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

    private function normalizeKehadiranInterviewValue($value): ?string
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

        if (in_array($normalized, ['tidak_respon', 'tidakrespon', 'no_response', 'noresponse'], true)) {
            return 'tidak_respon';
        }

        if (in_array($normalized, ['reschedule', 'rescheduled', 'jadwal_ulang', 'jadwalulang', 'ubah_jadwal', 'ubahjadwal'], true)) {
            return 'reschedule';
        }

        return null;
    }

    private function normalizeStatusReviewManagementValue($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = strtolower(trim((string) $value));
        $normalized = str_replace([' ', '-'], '_', $normalized);

        if (in_array($normalized, [
            'diterima',
            'terima',
            'lolos',
            'approved',
            'approve',
            'accept',
            'accepted',
            '1',
            'true',
            'ya',
            'yes',
        ], true)) {
            return 'diterima';
        }

        if (in_array($normalized, [
            'gagal',
            'ditolak',
            'tidak_diterima',
            'tidak_lolos',
            'reject',
            'rejected',
            'not_approved',
            '0',
            'false',
            'tidak',
            'no',
        ], true)) {
            return 'gagal';
        }

        return null;
    }

    private function normalizeHasilInterviewValue($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = strtolower(trim((string) $value));
        $normalized = str_replace([' ', '-'], '_', $normalized);

        if (in_array($normalized, [
            'tidak_lolos_interview',
            'tidak_lolos',
            'gagal',
            'gagal_interview',
            'failed',
            'fail',
            '0',
            'false',
            'tidak',
            'no',
        ], true)) {
            return 'tidak_lolos_interview';
        }

        if (in_array($normalized, [
            'lolos_interview',
            'lolos',
            'lulus',
            'passed',
            'pass',
            '1',
            'true',
            'ya',
            'yes',
        ], true)) {
            return 'lolos_interview';
        }

        if (in_array($normalized, [
            'dipertimbangkan',
            'di_pertimbangkan',
            'pertimbangkan',
            'dipertimbangakan',
        ], true)) {
            return 'dipertimbangkan';
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

        if (in_array($normalized, ['lolos', '1', 'true', 'ya', 'yes'], true)) {
            return 'lolos';
        }

        if (in_array($normalized, ['gagal', '0', 'false', 'tidak', 'no'], true)) {
            return 'gagal';
        }

        return null;
    }

    private function normalizeStatusOfferingLetterValue($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = strtolower(trim((string) $value));
        $normalized = str_replace([' ', '-'], '_', $normalized);

        if (in_array($normalized, ['menerima', 'terima', 'diterima', 'accept', 'accepted'], true)) {
            return 'Menerima';
        }

        if (in_array($normalized, ['menolak', 'tolak', 'ditolak', 'reject', 'rejected'], true)) {
            return 'Menolak';
        }

        if (in_array($normalized, ['tidak_melanjutkan', 'tidakmelanjutkan', 'tidak_lanjut', 'tidak_lanjutkan'], true)) {
            return 'Tidak Melanjutkan';
        }

        return null;
    }

    private function getSaranOfferingLetter(?string $statusJadwal): string
    {
        $status = $this->normalizeStatusOfferingLetterValue($statusJadwal);

        if ($status === 'Menerima') {
            return self::PESAN_OFFERING_LETTER_MENERIMA;
        }

        if ($status === 'Menolak') {
            return self::PESAN_OFFERING_LETTER_MENOLAK;
        }

        if ($status === 'Tidak Melanjutkan') {
            return self::PESAN_OFFERING_LETTER_TIDAK_MELANJUTKAN;
        }

        return self::PESAN_LOLOS_OFFERING_LETTER;
    }

    private function getLabelRelasi($model, array $columns, ?string $fallback = null): string
    {
        if ($model) {
            foreach ($columns as $column) {
                if (!empty($model->{$column})) {
                    return (string) $model->{$column};
                }
            }
        }

        if (!empty($fallback)) {
            return (string) $fallback;
        }

        return '-';
    }
}