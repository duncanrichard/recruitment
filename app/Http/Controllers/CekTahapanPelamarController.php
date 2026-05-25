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
        $bolehLanjutJadwalTestZoom = $formCompletion['completed_steps'] >= $formCompletion['total_steps'];

        $jadwalTest = JadwalTestZoom::query()
            ->where('data_riwayat_diri_id', $pelamar->id)
            ->orderByDesc('jadwal')
            ->first();

        $jadwalTestData = $this->formatJadwalTest($jadwalTest);
        $jadwalTestMmpiData = $this->formatJadwalTestMmpi($pelamar);

        $hasilTest = $jadwalTestData['hasil_test'] ?? null;
        $hasilTestMmpi = $jadwalTestMmpiData['hasil_test'] ?? null;
        $punyaJadwalTest = !empty($jadwalTestData);
        $punyaHasilTest = !empty($hasilTest);
        $punyaJadwalTestMmpi = !empty($jadwalTestMmpiData);
        $punyaHasilTestMmpi = !empty($hasilTestMmpi);
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
            $tahapan[] = [
                'nama' => 'Jadwal Test Zoom',
                'status' => $bolehLanjutJadwalTestZoom ? 'Terjadwal' : 'Terkunci',
                'keterangan' => $bolehLanjutJadwalTestZoom
                    ? 'Jadwal test Zoom kandidat sudah tersedia.'
                    : 'Jadwal test Zoom sudah tersedia, namun belum dapat dilanjutkan karena formulir pendaftaran belum lengkap sampai tahap Kesiapan Bekerja.',
                'saran' => $bolehLanjutJadwalTestZoom
                    ? 'Silakan mengikuti test Zoom sesuai jadwal yang sudah ditentukan.'
                    : $pesanBelumLengkap,
                'jadwal_test' => array_merge($jadwalTestData, [
                    'boleh_akses_jadwal_test_zoom' => $bolehLanjutJadwalTestZoom,
                    'bolehAksesJadwalTestZoom' => $bolehLanjutJadwalTestZoom,
                    'disabled' => !$bolehLanjutJadwalTestZoom,
                    'disabled_reason' => $bolehLanjutJadwalTestZoom ? null : $pesanBelumLengkap,
                    'disabledReason' => $bolehLanjutJadwalTestZoom ? null : $pesanBelumLengkap,
                ]),
                'jadwal_test_zoom' => array_merge($jadwalTestData, [
                    'boleh_akses_jadwal_test_zoom' => $bolehLanjutJadwalTestZoom,
                    'bolehAksesJadwalTestZoom' => $bolehLanjutJadwalTestZoom,
                    'disabled' => !$bolehLanjutJadwalTestZoom,
                    'disabled_reason' => $bolehLanjutJadwalTestZoom ? null : $pesanBelumLengkap,
                    'disabledReason' => $bolehLanjutJadwalTestZoom ? null : $pesanBelumLengkap,
                ]),
            ];
        }

        if ($punyaHasilTest) {
            $tahapan[] = [
                'nama' => 'Hasil Seleksi Test Zoom',
                'status' => $hasilTest === 'lolos' ? 'Lolos' : 'Gagal',
                'keterangan' => $hasilTest === 'lolos'
                    ? 'Selamat, Anda dinyatakan lolos pada seleksi test Zoom.'
                    : 'Mohon maaf, Anda dinyatakan belum lolos pada seleksi test Zoom.',
                'saran' => $hasilTest === 'lolos'
                    ? 'Silakan pantau informasi jadwal test MMPI dari tim rekrutmen.'
                    : 'Terima kasih sudah mengikuti proses seleksi.',
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

                if ($punyaHasilTestMmpi) {
                    $tahapan[] = [
                        'nama' => 'Hasil Seleksi Test MMPI',
                        'status' => $hasilTestMmpi === 'lolos' ? 'Lolos' : 'Gagal',
                        'keterangan' => $hasilTestMmpi === 'lolos'
                            ? 'Selamat, Anda dinyatakan lolos pada seleksi test MMPI.'
                            : 'Mohon maaf, Anda dinyatakan belum lolos pada seleksi test MMPI.',
                        'saran' => $hasilTestMmpi === 'lolos'
                            ? 'Silakan pantau informasi tahapan seleksi berikutnya dari tim rekrutmen.'
                            : 'Terima kasih sudah mengikuti proses seleksi.',
                        'hasil_test' => $hasilTestMmpi,
                        'hasilTest' => $hasilTestMmpi,
                        'hasil_test_mmpi' => $hasilTestMmpi,
                        'hasilTestMmpi' => $hasilTestMmpi,
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
            $status = $hasilTest === 'lolos'
                ? 'Lolos Seleksi Test Zoom'
                : 'Gagal Seleksi Test Zoom';

            $keterangan = $hasilTest === 'lolos'
                ? 'Kandidat dinyatakan lolos pada seleksi test Zoom.'
                : 'Kandidat dinyatakan belum lolos pada seleksi test Zoom.';

            $saran = $hasilTest === 'lolos'
                ? 'Silakan pantau informasi jadwal test MMPI dari tim rekrutmen.'
                : 'Terima kasih sudah mengikuti proses seleksi.';

            $tahapanTerakhir = 'Hasil Seleksi Test Zoom';
        }

        if ($punyaJadwalTestMmpi) {
            $status = 'Jadwal Test MMPI Tersedia';
            $keterangan = 'Kandidat sudah mendapatkan jadwal test MMPI.';
            $saran = 'Silakan mengikuti test MMPI sesuai jadwal yang sudah ditentukan.';
            $tahapanTerakhir = 'Jadwal Test MMPI';
        }

        if ($punyaHasilTestMmpi) {
            $status = $hasilTestMmpi === 'lolos'
                ? 'Lolos Seleksi Test MMPI'
                : 'Gagal Seleksi Test MMPI';

            $keterangan = $hasilTestMmpi === 'lolos'
                ? 'Kandidat dinyatakan lolos pada seleksi test MMPI.'
                : 'Kandidat dinyatakan belum lolos pada seleksi test MMPI.';

            $saran = $hasilTestMmpi === 'lolos'
                ? 'Silakan pantau informasi tahapan seleksi berikutnya dari tim rekrutmen.'
                : 'Terima kasih sudah mengikuti proses seleksi.';

            $tahapanTerakhir = 'Hasil Seleksi Test MMPI';
        }

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

            'posisi_dilamar' => $this->getLabelRelasi(
                $pelamar->posisi,
                [
                    'nama_posisi',
                    'posisi',
                    'nama_posisi_dilamar',
                    'posisi_dilamar',
                    'jabatan',
                    'nama_jabatan',
                    'nama',
                ],
                $pelamar->posisi_yang_dilamar ?? null
            ),

            'posisi_yang_dilamar' => $this->getLabelRelasi(
                $pelamar->posisi,
                [
                    'nama_posisi',
                    'posisi',
                    'nama_posisi_dilamar',
                    'posisi_dilamar',
                    'jabatan',
                    'nama_jabatan',
                    'nama',
                ],
                $pelamar->posisi_yang_dilamar ?? null
            ),

            'perusahaan_dilamar' => $this->getLabelRelasi(
                $pelamar->perusahaan,
                [
                    'nama_perusahaan',
                    'perusahaan',
                    'nama',
                ],
                $pelamar->perusahaan_dilamar ?? null
            ),

            'hasil_test' => $hasilTest,
            'hasilTest' => $hasilTest,
            'hasil_test_zoom' => $hasilTest,
            'hasilTestZoom' => $hasilTest,

            'hasil_test_mmpi' => $hasilTestMmpi,
            'hasilTestMmpi' => $hasilTestMmpi,
            'status_hasil_test_mmpi' => $hasilTestMmpi,
            'statusHasilTestMmpi' => $hasilTestMmpi,

            'jadwal_test_mmpi' => $jadwalTestMmpiData,
            'jadwalTestMmpi' => $jadwalTestMmpiData,
            'jadwal_mmpi' => $jadwalTestMmpiData,
            'jadwalMmpi' => $jadwalTestMmpiData,

            'jadwal_test' => $jadwalTestData ? array_merge($jadwalTestData, [
                'boleh_akses_jadwal_test_zoom' => $bolehLanjutJadwalTestZoom,
                'bolehAksesJadwalTestZoom' => $bolehLanjutJadwalTestZoom,
                'disabled' => !$bolehLanjutJadwalTestZoom,
                'disabled_reason' => $bolehLanjutJadwalTestZoom ? null : $pesanBelumLengkap,
                'disabledReason' => $bolehLanjutJadwalTestZoom ? null : $pesanBelumLengkap,
            ]) : null,
            'jadwal_test_zoom' => $jadwalTestData ? array_merge($jadwalTestData, [
                'boleh_akses_jadwal_test_zoom' => $bolehLanjutJadwalTestZoom,
                'bolehAksesJadwalTestZoom' => $bolehLanjutJadwalTestZoom,
                'disabled' => !$bolehLanjutJadwalTestZoom,
                'disabled_reason' => $bolehLanjutJadwalTestZoom ? null : $pesanBelumLengkap,
                'disabledReason' => $bolehLanjutJadwalTestZoom ? null : $pesanBelumLengkap,
            ]) : null,
            'jadwalTestZoom' => $jadwalTestData ? array_merge($jadwalTestData, [
                'boleh_akses_jadwal_test_zoom' => $bolehLanjutJadwalTestZoom,
                'bolehAksesJadwalTestZoom' => $bolehLanjutJadwalTestZoom,
                'disabled' => !$bolehLanjutJadwalTestZoom,
                'disabled_reason' => $bolehLanjutJadwalTestZoom ? null : $pesanBelumLengkap,
                'disabledReason' => $bolehLanjutJadwalTestZoom ? null : $pesanBelumLengkap,
            ]) : null,

            'tahapan' => $tahapan,

            'tahapan_seleksi' => [
                'jadwal_test' => $jadwalTestData,
                'jadwal_test_zoom' => $jadwalTestData,
                'jadwal_test_mmpi' => $jadwalTestMmpiData,
                'hasil_test' => $hasilTest,
                'hasil_test_zoom' => $hasilTest,
                'hasil_test_mmpi' => $hasilTestMmpi,
                'boleh_melanjutkan_jadwal_test_zoom' => $bolehLanjutJadwalTestZoom,
                'pesan_jadwal_test_zoom' => $bolehLanjutJadwalTestZoom ? null : $pesanBelumLengkap,
                'tahapan' => $tahapan,
            ],

            'tahapanSeleksi' => [
                'jadwal_test' => $jadwalTestData,
                'jadwal_test_zoom' => $jadwalTestData,
                'jadwal_test_mmpi' => $jadwalTestMmpiData,
                'hasil_test' => $hasilTest,
                'hasil_test_zoom' => $hasilTest,
                'hasil_test_mmpi' => $hasilTestMmpi,
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