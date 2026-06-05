<?php

namespace App\Http\Controllers;

use App\Models\DataRiwayatDiri;
use App\Models\DataRiwayatKeluarga;
use App\Models\DataRiwayatKesehatan;
use App\Models\DataRiwayatPekerjaan;
use App\Models\DataKesiapanBekerja;
use App\Models\DataSaudaraIpar;
use App\Models\DataSaudaraKandung;
use App\Models\OpsiKacamata;
use App\Models\SosialMedia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class PendaftaranController extends Controller
{
    private function pelamarQuery()
    {
        return DataRiwayatDiri::query()
            ->with([
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
                'jadwalTestZoom',
            ]);
    }

    public function index()
    {
        return view('pages.pendaftaran.index', [
            'title' => 'Pendaftaran',
            'token' => null,
            'pelamar' => null,
        ]);
    }

    public function show(string $token)
    {
        $pelamar = $this->pelamarQuery()
            ->where('token', $token)
            ->firstOrFail();

        return view('pages.pendaftaran.index', [
            'title' => 'Pendaftaran',
            'token' => $token,
            'pelamar' => $this->appendPelamarExtraData($pelamar),
        ]);
    }

    public function findByToken(string $token): JsonResponse
    {
        $pelamar = $this->pelamarQuery()
            ->where('token', $token)
            ->first();

        if (!$pelamar) {
            return response()->json([
                'success' => false,
                'message' => 'Token pelamar tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data pelamar ditemukan.',
            'data' => $this->appendPelamarExtraData($pelamar),
        ]);
    }


public function masterPendidikan(): JsonResponse
    {
        if (!Schema::hasTable('pendidikan')) {
            return response()->json([
                'success' => false,
                'message' => 'Tabel pendidikan tidak ditemukan.',
                'data' => [],
            ], 404);
        }

        $query = DB::table('pendidikan')
            ->select('id', 'pendidikan')
            ->whereNotNull('id')
            ->whereNotNull('pendidikan')
            ->orderBy('pendidikan');

        if (Schema::hasColumn('pendidikan', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        return response()->json([
            'success' => true,
            'data' => $query->get(),
        ]);
    }

    public function masterStatusPernikahan(): JsonResponse
    {
        if (!Schema::hasTable('status_pernikahan')) {
            return response()->json([
                'success' => false,
                'message' => 'Tabel status_pernikahan tidak ditemukan.',
                'data' => [],
            ], 404);
        }

        $query = DB::table('status_pernikahan')
            ->select('id', 'status_pernikahan')
            ->whereNotNull('id')
            ->whereNotNull('status_pernikahan')
            ->orderBy('status_pernikahan');

        if (Schema::hasColumn('status_pernikahan', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        return response()->json([
            'success' => true,
            'data' => $query->get(),
        ]);
    }

    public function masterPendaftaran(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'pendidikan' => $this->getMasterOptions(
                    ['pendidikan'],
                    ['pendidikan']
                ),

                'agama' => $this->getMasterOptions(
                    $this->tablesFor([
                        \App\Models\Agama::class,
                    ], [
                        'agama',
                        'data_agama',
                        'master_data_agama',
                        'master_agama',
                        'm_agama',
                    ]),
                    ['agama', 'nama_agama', 'nama']
                ),

                'status_pernikahan' => $this->getMasterOptions(
                    ['status_pernikahan'],
                    ['status_pernikahan', 'status', 'nama']
                ),

                'posisi' => $this->getPosisiOptions(),

                'kewarganegaraan' => $this->getMasterOptions(
                    $this->tablesFor([
                        \App\Models\Kewarganegaraan::class,
                    ], [
                        'kewarganegaraan',
                        'data_kewarganegaraan',
                        'master_data_kewarganegaraan',
                        'master_kewarganegaraan',
                    ]),
                    ['kewarganegaraan', 'nama_kewarganegaraan', 'nama']
                ),

                'jenis_kelamin' => $this->getColumnOptions(
                    'data_riwayat_diri',
                    'jenis_kelamin',
                    ['Laki-laki', 'Perempuan']
                ),

                'str_aktif' => $this->getStrAktifOptions(),

                'sosial_media' => $this->getHardcodedSosialMediaPlatforms(),

                'opsi_kacamata' => $this->getMasterOptions(
                    ['opsi_kacamata'],
                    ['opsi']
                ),
            ],
        ]);
    }

    public function updateDataDiriByToken(Request $request, string $token): JsonResponse
    {
        $pelamar = DataRiwayatDiri::query()
            ->where('token', $token)
            ->first();

        if (!$pelamar) {
            return response()->json([
                'success' => false,
                'message' => 'Token pelamar tidak ditemukan.',
            ], 404);
        }

        $request->merge([
            'str_aktif' => $this->normalizeStrAktif($request->input('str_aktif')),

            'alamat_domisili' => $request->input('alamat_domisili')
                ?: $request->input('alamat')
                ?: null,

            'alamat' => $request->input('alamat')
                ?: $request->input('alamat_domisili')
                ?: null,

            'status_pernikahan_id' => $request->input('status_pernikahan_id')
                ?: $request->input('status_perkawinan')
                ?: null,

            'status_perkawinan' => $request->input('status_perkawinan')
                ?: $request->input('status_pernikahan_id')
                ?: null,

            'provinsi_id' => $request->input('provinsi_id')
                ?: $request->input('provinsi')
                ?: null,

            'kabupaten_id' => $request->input('kabupaten_id')
                ?: $request->input('kabupaten')
                ?: null,

            'kecamatan_id' => $request->input('kecamatan_id')
                ?: $request->input('kecamatan')
                ?: null,

            'kelurahan_id' => $request->input('kelurahan_id')
                ?: $request->input('kelurahan')
                ?: null,
        ]);

        $posisiIdUntukStr = $this->getPosisiIdFromRequestOrPelamar(
            $request->input('posisi_dilamar'),
            $pelamar
        );

        $wajibStr = $this->isPosisiWajibStr($posisiIdUntukStr);

        $validated = $request->validate([
            'posisi_dilamar' => ['nullable', 'string', 'max:255'],
            'perusahaan_dilamar' => ['nullable', 'string', 'max:255'],

            'nama' => ['required', 'string', 'max:255'],
            'nama_panggilan' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'no_hp' => ['required', 'string', 'max:50'],

            'pendidikan' => ['required', 'string', 'max:255'],
            'jurusan' => ['nullable', 'string', 'max:255'],
            'nama_institusi' => ['nullable', 'string', 'max:255'],

            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['required', 'date'],
            'jenis_kelamin' => ['nullable', 'string', 'max:50'],

            'agama' => ['required', 'string', 'max:255'],

            'status_pernikahan_id' => ['nullable', 'string', 'max:255'],
            'status_perkawinan' => ['nullable', 'string', 'max:255'],

            'kewarganegaraan' => ['nullable', 'string', 'max:255'],
            'str_aktif' => [$wajibStr ? 'required' : 'nullable', 'string', 'max:255'],

            'alamat' => ['nullable', 'string'],
            'alamat_ktp' => ['required', 'string'],
            'alamat_domisili' => ['required', 'string'],

            'provinsi_id' => ['nullable', 'string', 'max:50'],
            'kabupaten_id' => ['nullable', 'string', 'max:50'],
            'kecamatan_id' => ['nullable', 'string', 'max:50'],
            'kelurahan_id' => ['nullable', 'string', 'max:50'],

            'provinsi' => ['nullable', 'string', 'max:50'],
            'kabupaten' => ['nullable', 'string', 'max:50'],
            'kecamatan' => ['nullable', 'string', 'max:50'],
            'kelurahan' => ['nullable', 'string', 'max:50'],

            'rt' => ['nullable', 'string', 'max:10'],
            'rw' => ['nullable', 'string', 'max:10'],

            'sumber_informasi' => ['nullable', 'string', 'max:255'],

            'sosial_media' => ['nullable', 'array'],
            'sosial_media.*.id' => ['nullable', 'string', 'max:255'],
            'sosial_media.*.platform' => ['nullable', 'string', 'max:100'],
            'sosial_media.*.nama_akun' => ['nullable', 'string', 'max:255'],
            'sosial_media.*.nama_account' => ['nullable', 'string', 'max:255'],
        ], [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'nama_panggilan.required' => 'Nama panggilan wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'no_hp.required' => 'Nomor HP wajib diisi.',
            'pendidikan.required' => 'Pendidikan terakhir wajib diisi.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'agama.required' => 'Agama wajib diisi.',
            'alamat_ktp.required' => 'Alamat KTP wajib diisi.',
            'alamat_domisili.required' => 'Alamat domisili wajib diisi.',
            'str_aktif.required' => 'STR Aktif wajib diisi untuk posisi Perawat atau Dokter.',
        ]);

        $wilayahError = $this->validateWilayahBertingkat(
            $validated['provinsi_id'] ?? null,
            $validated['kabupaten_id'] ?? null,
            $validated['kecamatan_id'] ?? null,
            $validated['kelurahan_id'] ?? null
        );

        if ($wilayahError) {
            return $wilayahError;
        }

        $table = $pelamar->getTable();

        $pendidikanId = $this->findPendidikanId($validated['pendidikan'] ?? null);

        if (!$pendidikanId) {
            return $this->masterError(
                'pendidikan',
                'Pendidikan yang dipilih tidak ditemukan di tabel pendidikan. Pastikan dropdown pendidikan mengambil data dari tabel pendidikan dan mengirim id UUID.'
            );
        }

        $agamaId = $this->findMasterId(
            $this->tablesFor([\App\Models\Agama::class], [
                'agama',
                'data_agama',
                'master_data_agama',
                'master_agama',
                'm_agama',
            ]),
            ['agama', 'nama_agama', 'nama'],
            $validated['agama'] ?? null
        );

        if (!$agamaId) {
            return $this->masterError(
                'agama',
                'Agama yang dipilih tidak ditemukan di master agama.'
            );
        }

        $statusPernikahanValue =
            $validated['status_pernikahan_id']
            ?? $validated['status_perkawinan']
            ?? null;

        $statusPernikahanId = null;

        if ($statusPernikahanValue) {
            $statusPernikahanId = $this->findStatusPernikahanId($statusPernikahanValue);

            if (!$statusPernikahanId) {
                return $this->masterError(
                    'status_pernikahan_id',
                    'Status pernikahan yang dipilih tidak ditemukan di tabel status_pernikahan. Pastikan dropdown status pernikahan mengambil data dari tabel status_pernikahan dan mengirim id UUID.'
                );
            }
        }

        $kewarganegaraanId = null;

        if (!empty($validated['kewarganegaraan'])) {
            $kewarganegaraanId = $this->findMasterId(
                $this->tablesFor([\App\Models\Kewarganegaraan::class], [
                    'kewarganegaraan',
                    'data_kewarganegaraan',
                    'master_data_kewarganegaraan',
                    'master_kewarganegaraan',
                ]),
                ['kewarganegaraan', 'nama_kewarganegaraan', 'nama'],
                $validated['kewarganegaraan']
            );
        }

        $sumberInformasiId = null;

        if (!empty($validated['sumber_informasi'])) {
            $sumberInformasiId = $this->findMasterId(
                $this->tablesFor([\App\Models\SumberInformasi::class], [
                    'sumber_informasi',
                    'data_sumber_informasi',
                    'master_data_sumber_informasi',
                ]),
                ['informasi', 'sumber_informasi', 'nama'],
                $validated['sumber_informasi']
            );
        }

        $posisiId = null;

        if (!empty($validated['posisi_dilamar'])) {
            $posisiId = $this->findMasterId(
                $this->tablesFor([\App\Models\Posisi::class], [
                    'posisi',
                    'data_posisi',
                    'master_data_posisi',
                    'master_posisi',
                ]),
                [
                    'nama_posisi',
                    'posisi',
                    'nama_posisi_dilamar',
                    'posisi_dilamar',
                    'jabatan',
                    'nama_jabatan',
                    'nama',
                ],
                $validated['posisi_dilamar']
            );
        }

        $perusahaanId = null;

        if (!empty($validated['perusahaan_dilamar'])) {
            $perusahaanId = $this->findMasterId(
                $this->tablesFor([\App\Models\DataPerusahaan::class], [
                    'data_perusahaan',
                    'perusahaan',
                    'master_data_perusahaan',
                    'master_perusahaan',
                ]),
                ['nama_perusahaan', 'perusahaan', 'nama'],
                $validated['perusahaan_dilamar']
            );
        }

        DB::transaction(function () use (
            $pelamar,
            $table,
            $validated,
            $wajibStr,
            $pendidikanId,
            $agamaId,
            $statusPernikahanId,
            $kewarganegaraanId,
            $sumberInformasiId,
            $posisiId,
            $perusahaanId
        ) {
            $data = [
                'nama_lengkap' => $validated['nama'] ?? null,
                'nama_panggil' => $validated['nama_panggilan'] ?? null,
                'email' => $validated['email'] ?? null,
                'no_wa' => $validated['no_hp'] ?? null,

                'pendidikan_id' => $pendidikanId,
                'jurusan' => $validated['jurusan'] ?? null,
                'nama_institusi' => $validated['nama_institusi'] ?? null,

                'agama_id' => $agamaId,
                'status_pernikahan_id' => $statusPernikahanId,
                'kewarganegaraan_id' => $kewarganegaraanId,
                'sumber_informasi_id' => $sumberInformasiId,

                'tempat_lahir' => $validated['tempat_lahir'] ?? null,
                'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
                'jenis_kelamin' => $validated['jenis_kelamin'] ?? null,

                'str_aktif' => $wajibStr ? ($validated['str_aktif'] ?? null) : null,

                'alamat_ktp' => $validated['alamat_ktp'] ?? null,
                'alamat_domisili' => $validated['alamat_domisili'] ?? null,

                'provinsi_id' => $validated['provinsi_id'] ?? null,
                'kabupaten_id' => $validated['kabupaten_id'] ?? null,
                'kecamatan_id' => $validated['kecamatan_id'] ?? null,
                'kelurahan_id' => $validated['kelurahan_id'] ?? null,

                'rt' => $validated['rt'] ?? null,
                'rw' => $validated['rw'] ?? null,
            ];

            if (Schema::hasColumn($table, 'alamat')) {
                $data['alamat'] =
                    $validated['alamat_domisili']
                    ?? $validated['alamat_ktp']
                    ?? $validated['alamat']
                    ?? null;
            }

            if ($posisiId) {
                $this->putFirstExistingColumn(
                    $data,
                    $table,
                    ['posisi_dilamar', 'posisi_yang_dilamar'],
                    $posisiId
                );
            }

            if ($perusahaanId) {
                $this->putFirstExistingColumn(
                    $data,
                    $table,
                    ['perusahaan_dilamar'],
                    $perusahaanId
                );
            }

            $data = collect($data)
                ->filter(fn ($value, $column) => Schema::hasColumn($table, $column))
                ->toArray();

            $pelamar->forceFill($data);
            $pelamar->save();

            $this->syncSosialMedia($pelamar, $validated['sosial_media'] ?? []);
        });

        $pelamar = $this->pelamarQuery()
            ->where('token', $token)
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Data diri berhasil diperbarui.',
            'data' => $this->appendPelamarExtraData($pelamar),
        ]);
    }

    public function updateRiwayatKeluargaByToken(Request $request, string $token): JsonResponse
    {
        $pelamar = DataRiwayatDiri::query()
            ->where('token', $token)
            ->first();

        if (!$pelamar) {
            return response()->json([
                'success' => false,
                'message' => 'Token pelamar tidak ditemukan.',
            ], 404);
        }

        $validated = $request->validate([
            'hubungan_kerabat_instansi' => ['nullable', 'array'],
            'hubungan_kerabat_instansi.*' => ['nullable', 'string', 'max:255'],

            'nama_ayah_kandung' => ['nullable', 'string', 'max:255'],
            'pekerjaan_ayah_kandung' => ['nullable', 'string', 'max:255'],
            'nama_ibu_kandung' => ['nullable', 'string', 'max:255'],
            'pekerjaan_ibu_kandung' => ['nullable', 'string', 'max:255'],

            'nama_ayah' => ['nullable', 'string', 'max:255'],
            'nik_ayah' => ['nullable', 'string', 'max:50'],
            'tempat_lahir_ayah' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir_ayah' => ['nullable', 'date'],
            'pekerjaan_ayah' => ['nullable', 'string', 'max:255'],
            'no_hp_ayah' => ['nullable', 'string', 'max:50'],
            'alamat_ayah' => ['nullable', 'string'],

            'nama_ibu' => ['nullable', 'string', 'max:255'],
            'nik_ibu' => ['nullable', 'string', 'max:50'],
            'tempat_lahir_ibu' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir_ibu' => ['nullable', 'date'],
            'pekerjaan_ibu' => ['nullable', 'string', 'max:255'],
            'no_hp_ibu' => ['nullable', 'string', 'max:50'],
            'alamat_ibu' => ['nullable', 'string'],

            'nama_suami_istri' => ['nullable', 'string', 'max:255'],
            'pekerjaan_suami_istri' => ['nullable', 'string', 'max:255'],
            'tlpn_suami_istri' => ['nullable', 'string', 'max:50'],

            'nama_bapak_mertua' => ['nullable', 'string', 'max:255'],
            'pekerjaan_bapak_mertua' => ['nullable', 'string', 'max:255'],
            'nama_ibu_mertua' => ['nullable', 'string', 'max:255'],
            'pekerjaan_ibu_mertua' => ['nullable', 'string', 'max:255'],

            'kontak_darurat' => ['nullable', 'array'],
            'kontak_darurat.*.nama' => ['nullable', 'string', 'max:255'],
            'kontak_darurat.*.status' => ['nullable', 'string', 'max:255'],
            'kontak_darurat.*.nomor' => ['nullable', 'string', 'max:50'],

            'saudara_kandung' => ['nullable', 'array'],
            'saudara_kandung.*.id' => ['nullable', 'string', 'max:255'],
            'saudara_kandung.*.nama' => ['nullable', 'string', 'max:255'],
            'saudara_kandung.*.jenis_kelamin' => ['nullable', 'string', 'max:50'],
            'saudara_kandung.*.hubungan' => ['nullable', 'string', 'max:100'],
            'saudara_kandung.*.pekerjaan' => ['nullable', 'string', 'max:255'],
            'saudara_kandung.*.no_hp' => ['nullable', 'string', 'max:50'],
            'saudara_kandung.*.alamat' => ['nullable', 'string'],

            'saudara_ipar' => ['nullable', 'array'],
            'saudara_ipar.*.id' => ['nullable', 'string', 'max:255'],
            'saudara_ipar.*.nama' => ['nullable', 'string', 'max:255'],
            'saudara_ipar.*.jenis_kelamin' => ['nullable', 'string', 'max:50'],
            'saudara_ipar.*.hubungan' => ['nullable', 'string', 'max:100'],
            'saudara_ipar.*.pekerjaan' => ['nullable', 'string', 'max:255'],
            'saudara_ipar.*.no_hp' => ['nullable', 'string', 'max:50'],
            'saudara_ipar.*.alamat' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($pelamar, $validated) {
            $riwayatKeluarga = DataRiwayatKeluarga::query()
                ->where('data_riwayat_diri_id', $pelamar->id)
                ->first();

            if (!$riwayatKeluarga) {
                $riwayatKeluarga = new DataRiwayatKeluarga();
                $riwayatKeluarga->data_riwayat_diri_id = $pelamar->id;
            }

            $table = $riwayatKeluarga->getTable();

            $hubunganKerabat = $this->normalizeArrayInput(
                $validated['hubungan_kerabat_instansi'] ?? []
            );

            $kontakDarurat = $this->normalizeKontakDarurat(
                $validated['kontak_darurat'] ?? []
            );

            $data = [
                'data_riwayat_diri_id' => $pelamar->id,

                'nama_ayah_kandung' => $validated['nama_ayah_kandung'] ?? null,
                'pekerjaan_ayah_kandung' => $validated['pekerjaan_ayah_kandung'] ?? null,
                'nama_ibu_kandung' => $validated['nama_ibu_kandung'] ?? null,
                'pekerjaan_ibu_kandung' => $validated['pekerjaan_ibu_kandung'] ?? null,

                'nama_ayah' => $validated['nama_ayah'] ?? null,
                'nik_ayah' => $validated['nik_ayah'] ?? null,
                'tempat_lahir_ayah' => $validated['tempat_lahir_ayah'] ?? null,
                'tanggal_lahir_ayah' => $validated['tanggal_lahir_ayah'] ?? null,
                'pekerjaan_ayah' => $validated['pekerjaan_ayah'] ?? null,
                'no_hp_ayah' => $validated['no_hp_ayah'] ?? null,
                'alamat_ayah' => $validated['alamat_ayah'] ?? null,

                'nama_ibu' => $validated['nama_ibu'] ?? null,
                'nik_ibu' => $validated['nik_ibu'] ?? null,
                'tempat_lahir_ibu' => $validated['tempat_lahir_ibu'] ?? null,
                'tanggal_lahir_ibu' => $validated['tanggal_lahir_ibu'] ?? null,
                'pekerjaan_ibu' => $validated['pekerjaan_ibu'] ?? null,
                'no_hp_ibu' => $validated['no_hp_ibu'] ?? null,
                'alamat_ibu' => $validated['alamat_ibu'] ?? null,

                'nama_suami_istri' => $validated['nama_suami_istri'] ?? null,
                'pekerjaan_suami_istri' => $validated['pekerjaan_suami_istri'] ?? null,
                'tlpn_suami_istri' => $validated['tlpn_suami_istri'] ?? null,

                'nama_bapak_mertua' => $validated['nama_bapak_mertua'] ?? null,
                'pekerjaan_bapak_mertua' => $validated['pekerjaan_bapak_mertua'] ?? null,
                'nama_ibu_mertua' => $validated['nama_ibu_mertua'] ?? null,
                'pekerjaan_ibu_mertua' => $validated['pekerjaan_ibu_mertua'] ?? null,

                'hubungan_kerabat_instansi' => $hubunganKerabat,
                'kontak_darurat' => $kontakDarurat,
            ];

            if (Schema::hasColumn($table, 'kerabat_bekerja_diinstansi')) {
                $data['kerabat_bekerja_diinstansi'] = implode(', ', $hubunganKerabat);
            }

            if (Schema::hasColumn($table, 'tlpn_darurat')) {
                $firstKontak = $kontakDarurat[0] ?? null;
                $data['tlpn_darurat'] = $firstKontak['nomor'] ?? null;
            }

            if (
                !Schema::hasColumn($table, 'pekerjaan_suami_istri') &&
                Schema::hasColumn($table, 'pekerjaan_sumi_istri')
            ) {
                $data['pekerjaan_sumi_istri'] = $validated['pekerjaan_suami_istri'] ?? null;
            }

            $data = collect($data)
                ->filter(fn ($value, $column) => Schema::hasColumn($table, $column))
                ->toArray();

            $riwayatKeluarga->forceFill($data);
            $riwayatKeluarga->save();

            $this->syncSaudaraKandung(
                $pelamar,
                $riwayatKeluarga,
                $validated['saudara_kandung'] ?? []
            );

            $this->syncSaudaraIpar(
                $pelamar,
                $riwayatKeluarga,
                $validated['saudara_ipar'] ?? []
            );
        });

        $pelamar = $this->pelamarQuery()
            ->where('token', $token)
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Riwayat keluarga berhasil diperbarui.',
            'data' => $this->appendPelamarExtraData($pelamar),
        ]);
    }

    public function updateRiwayatKesehatanByToken(Request $request, string $token): JsonResponse
    {
        $pelamar = DataRiwayatDiri::query()
            ->where('token', $token)
            ->first();

        if (!$pelamar) {
            return response()->json([
                'success' => false,
                'message' => 'Token pelamar tidak ditemukan.',
            ], 404);
        }

        $request->merge([
            'gol_darah' => $request->input('gol_darah')
                ?: $request->input('golongan_darah')
                ?: null,

            'alat_bantu_dengar' => $request->input('alat_bantu_dengar')
                ?: $request->input('alat_bantu_pendengaran')
                ?: null,

            'menulis_dengan_tangan' => $request->input('menulis_dengan_tangan')
                ?: $request->input('tangan_dominan')
                ?: null,

            'sering_gemetar' => $request->input('sering_gemetar')
                ?: $request->input('tangan_gemetar')
                ?: null,

            'tangan_sering_berkeringat' => $request->input('tangan_sering_berkeringat')
                ?: $request->input('tangan_berkeringat')
                ?: null,

            'penyakit_menular' => $request->input('penyakit_menular')
                ?: $request->input('riwayat_penyakit_menular')
                ?: null,

            'punya_alergi' => $request->input('punya_alergi')
                ?: $request->input('memiliki_alergi')
                ?: null,

            'nama_alergi' => $request->input('nama_alergi')
                ?: $request->input('alergi')
                ?: null,
        ]);

        $validated = $request->validate([
            'gol_darah' => ['nullable', 'string', 'max:255'],
            'golongan_darah' => ['nullable', 'string', 'max:255'],
            'tinggi_badan' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'berat_badan' => ['nullable', 'numeric', 'min:0', 'max:500'],

            'buta_warna' => ['nullable', 'string', 'max:255'],
            'opsi_kacamata_id' => ['nullable', 'string', 'max:255'],
            'alat_bantu_dengar' => ['nullable', 'string', 'max:255'],
            'menulis_dengan_tangan' => ['nullable', 'string', 'max:255'],
            'sering_gemetar' => ['nullable', 'string', 'max:255'],
            'tangan_sering_berkeringat' => ['nullable', 'string', 'max:255'],

            'penyakit_menular' => ['nullable', 'string', 'max:255'],
            'program_kehamilan' => ['nullable', 'string', 'max:255'],

            'punya_alergi' => ['nullable', 'string', 'max:255'],
            'nama_alergi' => ['nullable', 'string'],

            'punya_penyakit_genetik' => ['nullable', 'string', 'max:255'],
            'nama_penyakit' => ['nullable', 'string'],
            'riwayat_kronis' => ['nullable', 'string', 'max:255'],

            'pengobatan_psikolog' => ['nullable', 'string', 'max:255'],
            'kapan_dilakukan' => ['nullable', 'string'],

            'pernah_kecelakaan' => ['nullable', 'string', 'max:255'],
            'bagian_tubuh_kecelakaan' => ['nullable', 'string'],

            'pernah_operasi' => ['nullable', 'string', 'max:255'],
            'diagnosa_dokter' => ['nullable', 'string'],
        ]);

        if (!empty($validated['opsi_kacamata_id'])) {
            $opsiKacamataExists = OpsiKacamata::query()
                ->where('id', $validated['opsi_kacamata_id'])
                ->exists();

            if (!$opsiKacamataExists) {
                return $this->masterError(
                    'opsi_kacamata_id',
                    'Opsi kacamata yang dipilih tidak ditemukan.'
                );
            }
        }

        DB::transaction(function () use ($pelamar, $validated) {
            $riwayatKesehatan = DataRiwayatKesehatan::query()
                ->where('data_riwayat_diri_id', $pelamar->id)
                ->first();

            if (!$riwayatKesehatan) {
                $riwayatKesehatan = new DataRiwayatKesehatan();
                $riwayatKesehatan->data_riwayat_diri_id = $pelamar->id;
            }

            $table = $riwayatKesehatan->getTable();

            $data = [
                'data_riwayat_diri_id' => $pelamar->id,
                'buta_warna' => $validated['buta_warna'] ?? null,
                'opsi_kacamata_id' => $validated['opsi_kacamata_id'] ?? null,
                'alat_bantu_dengar' => $validated['alat_bantu_dengar'] ?? null,
                'menulis_dengan_tangan' => $validated['menulis_dengan_tangan'] ?? null,
                'sering_gemetar' => $validated['sering_gemetar'] ?? null,
                'tangan_sering_berkeringat' => $validated['tangan_sering_berkeringat'] ?? null,
                'penyakit_menular' => $validated['penyakit_menular'] ?? null,
                'program_kehamilan' => $validated['program_kehamilan'] ?? null,
                'punya_alergi' => $validated['punya_alergi'] ?? null,
                'nama_alergi' => $validated['nama_alergi'] ?? null,
                'punya_penyakit_genetik' => $validated['punya_penyakit_genetik'] ?? null,
                'nama_penyakit' => $validated['nama_penyakit'] ?? null,
                'riwayat_kronis' => $validated['riwayat_kronis'] ?? null,
                'pengobatan_psikolog' => $validated['pengobatan_psikolog'] ?? null,
                'kapan_dilakukan' => $validated['kapan_dilakukan'] ?? null,
                'pernah_kecelakaan' => $validated['pernah_kecelakaan'] ?? null,
                'bagian_tubuh_kecelakaan' => $validated['bagian_tubuh_kecelakaan'] ?? null,
                'pernah_operasi' => $validated['pernah_operasi'] ?? null,
                'diagnosa_dokter' => $validated['diagnosa_dokter'] ?? null,
            ];

            $data = collect($data)
                ->filter(fn ($value, $column) => Schema::hasColumn($table, $column))
                ->toArray();

            $riwayatKesehatan->forceFill($data);
            $riwayatKesehatan->save();

            $dataRiwayatDiriTable = $pelamar->getTable();

            $diriData = [
                'gol_darah' => $validated['gol_darah'] ?? $validated['golongan_darah'] ?? null,
                'tinggi_badan' => $validated['tinggi_badan'] ?? null,
                'berat_badan' => $validated['berat_badan'] ?? null,
            ];

            $diriData = collect($diriData)
                ->filter(fn ($value, $column) => $value !== null && Schema::hasColumn($dataRiwayatDiriTable, $column))
                ->toArray();

            if (count($diriData) > 0) {
                $pelamar->forceFill($diriData);
                $pelamar->save();
            }
        });

        $pelamar = $this->pelamarQuery()
            ->where('token', $token)
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Riwayat kesehatan berhasil diperbarui.',
            'data' => $this->appendPelamarExtraData($pelamar),
        ]);
    }


    public function updateRiwayatPekerjaanByToken(Request $request, string $token): JsonResponse
    {
        $pelamar = DataRiwayatDiri::query()
            ->where('token', $token)
            ->first();

        if (!$pelamar) {
            return response()->json([
                'success' => false,
                'message' => 'Token pelamar tidak ditemukan.',
            ], 404);
        }

        $tahunMulai = $this->normalizeYearValue($request->input('tahun_mulai_bekerja'));
        $tahunSelesai = $this->normalizeYearValue($request->input('tahun_selesai_bekerja'));

        $periodeKerjaAwal = $request->input('periode_kerja_awal')
            ?: $this->yearToDate($tahunMulai);

        $periodeKerjaAkhir = $request->input('periode_kerja_akhir')
            ?: $this->yearToDate($tahunSelesai);

        $posisiPekerjaan = $request->input('posisi_pekerjaan')
            ?: $request->input('posisi_pekerjaan_terakhir')
            ?: null;

        $request->merge([
            'posisi_pekerjaan' => $posisiPekerjaan,
            'posisi_pekerjaan_terakhir' => $request->input('posisi_pekerjaan_terakhir')
                ?: $posisiPekerjaan,
            'tahun_mulai_bekerja' => $tahunMulai,
            'tahun_selesai_bekerja' => $tahunSelesai,
            'periode_kerja_awal' => $periodeKerjaAwal,
            'periode_kerja_akhir' => $periodeKerjaAkhir,
            'lama_bekerja' => $this->calculateLamaBekerjaFromYear($tahunMulai, $tahunSelesai),
        ]);

        $validated = $request->validate([
            'nama_perusahaan' => ['nullable', 'string', 'max:255'],
            'posisi_pekerjaan_terakhir' => ['nullable', 'string', 'max:255'],
            'periode_kerja_awal' => ['nullable', 'date'],
            'periode_kerja_akhir' => ['nullable', 'date', 'after_or_equal:periode_kerja_awal'],
            'gaji_terakhir' => ['nullable', 'numeric', 'min:0', 'max:999999999999999999.99'],

            'status_pekerjaan' => ['required', 'string', 'max:100'],
            'posisi_pekerjaan' => ['nullable', 'string', 'max:255'],
            'bidang_pekerjaan' => ['nullable', 'string', 'max:255'],
            'lokasi_perusahaan' => ['nullable', 'string', 'max:255'],
            'tahun_mulai_bekerja' => ['nullable', 'digits:4'],
            'tahun_selesai_bekerja' => ['nullable', 'digits:4'],
            'lama_bekerja' => ['nullable', 'string', 'max:100'],
            'deskripsi_pekerjaan' => ['nullable', 'string'],
            'alasan_berhenti' => ['nullable', 'string'],
            'keahlian' => ['nullable', 'string'],
            'catatan_pekerjaan' => ['nullable', 'string'],

            'refrensi_kerja' => ['nullable', 'string', 'max:255'],
            'nama_refrensi' => ['nullable', 'string', 'max:255'],
            'telp_refrensi' => ['nullable', 'string', 'max:50'],

            'refrensi_rekan_kerja' => ['nullable', 'string', 'max:255'],
            'nama_refrensi_rekan' => ['nullable', 'string', 'max:255'],
            'telp_refrensi_rekan' => ['nullable', 'string', 'max:50'],

            'refrensi_kerabat' => ['nullable', 'string', 'max:255'],
            'nama_refrensi_kerabat' => ['nullable', 'string', 'max:255'],
            'telp_refrensi_kerabat' => ['nullable', 'string', 'max:50'],
        ], [
            'status_pekerjaan.required' => 'Status pekerjaan wajib diisi.',
            'periode_kerja_awal.date' => 'Periode kerja awal harus berupa tanggal yang valid.',
            'periode_kerja_akhir.date' => 'Periode kerja akhir harus berupa tanggal yang valid.',
            'periode_kerja_akhir.after_or_equal' => 'Periode kerja akhir tidak boleh lebih kecil dari periode kerja awal.',
            'gaji_terakhir.numeric' => 'Gaji terakhir harus berupa angka.',
            'gaji_terakhir.max' => 'Gaji terakhir terlalu besar.',
            'tahun_mulai_bekerja.digits' => 'Tahun mulai bekerja harus 4 digit.',
            'tahun_selesai_bekerja.digits' => 'Tahun selesai bekerja harus 4 digit.',
        ]);

        DB::transaction(function () use ($pelamar, $validated) {
            $riwayatPekerjaan = DataRiwayatPekerjaan::query()
                ->where('data_riwayat_diri_id', $pelamar->id)
                ->first();

            if (!$riwayatPekerjaan) {
                $riwayatPekerjaan = new DataRiwayatPekerjaan();
                $riwayatPekerjaan->data_riwayat_diri_id = $pelamar->id;
            }

            $table = $riwayatPekerjaan->getTable();

            $posisiPekerjaan = $validated['posisi_pekerjaan']
                ?? $validated['posisi_pekerjaan_terakhir']
                ?? null;

            $tahunMulai = $validated['tahun_mulai_bekerja'] ?? null;
            $tahunSelesai = $validated['tahun_selesai_bekerja'] ?? null;

            $periodeKerjaAwal = $this->normalizeRiwayatPekerjaanDate(
                $validated['periode_kerja_awal'] ?? $this->yearToDate($tahunMulai)
            );

            $periodeKerjaAkhir = $this->normalizeRiwayatPekerjaanDate(
                $validated['periode_kerja_akhir'] ?? $this->yearToDate($tahunSelesai)
            );

            $data = [
                'data_riwayat_diri_id' => $pelamar->id,
                'nama_perusahaan' => $validated['nama_perusahaan'] ?? null,
                'posisi_pekerjaan_terakhir' => $posisiPekerjaan,
                'periode_kerja_awal' => $periodeKerjaAwal,
                'periode_kerja_akhir' => $periodeKerjaAkhir,
                'gaji_terakhir' => $this->normalizeDecimalValue($validated['gaji_terakhir'] ?? null),

                'status_pekerjaan' => $validated['status_pekerjaan'] ?? null,
                'posisi_pekerjaan' => $posisiPekerjaan,
                'bidang_pekerjaan' => $validated['bidang_pekerjaan'] ?? null,
                'lokasi_perusahaan' => $validated['lokasi_perusahaan'] ?? null,
                'tahun_mulai_bekerja' => $tahunMulai,
                'tahun_selesai_bekerja' => $tahunSelesai,
                'lama_bekerja' => $this->calculateLamaBekerjaFromYear($tahunMulai, $tahunSelesai),
                'deskripsi_pekerjaan' => $validated['deskripsi_pekerjaan'] ?? null,
                'alasan_berhenti' => $validated['alasan_berhenti'] ?? null,
                'keahlian' => $validated['keahlian'] ?? null,
                'catatan_pekerjaan' => $validated['catatan_pekerjaan'] ?? null,

                'refrensi_kerja' => $validated['refrensi_kerja'] ?? null,
                'nama_refrensi' => $validated['nama_refrensi'] ?? null,
                'telp_refrensi' => $validated['telp_refrensi'] ?? null,

                'refrensi_rekan_kerja' => $validated['refrensi_rekan_kerja'] ?? null,
                'nama_refrensi_rekan' => $validated['nama_refrensi_rekan'] ?? null,
                'telp_refrensi_rekan' => $validated['telp_refrensi_rekan'] ?? null,

                'refrensi_kerabat' => $validated['refrensi_kerabat'] ?? null,
                'nama_refrensi_kerabat' => $validated['nama_refrensi_kerabat'] ?? null,
                'telp_refrensi_kerabat' => $validated['telp_refrensi_kerabat'] ?? null,
            ];

            $data = collect($data)
                ->filter(fn ($value, $column) => Schema::hasColumn($table, $column))
                ->toArray();

            $riwayatPekerjaan->forceFill($data);
            $riwayatPekerjaan->save();
        });

        $pelamar = $this->pelamarQuery()
            ->where('token', $token)
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Riwayat pekerjaan berhasil diperbarui.',
            'data' => $this->appendPelamarExtraData($pelamar),
        ]);
    }


    public function updateKesiapanBekerjaByToken(Request $request, string $token): JsonResponse
    {
        $pelamar = DataRiwayatDiri::query()
            ->where('token', $token)
            ->first();

        if (!$pelamar) {
            return response()->json([
                'success' => false,
                'message' => 'Token pelamar tidak ditemukan.',
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | Kesiapan Bekerja - sesuai field tabel terbaru
        |--------------------------------------------------------------------------
        | Field yang dipakai:
        | - kapan_siap_bekerja
        | - ekpetasi_gaji
        | - penempatan
        | - proses_bkhang
        | - dapat_dipertanggung_jawabkan
        | - bersedia_training
        |
        | Alias dari form lama/React tetap diterima agar tidak memutus frontend.
        */
        $kapanSiapBekerja = $this->normalizeKesiapanText(
            $request->input('kapan_siap_bekerja')
                ?: $request->input('tanggal_siap_kerja')
        );

        $expetasiGaji = $this->normalizeKesiapanDecimal(
            $request->input('ekpetasi_gaji')
                ?: $request->input('gaji_diharapkan')
        );

        $penempatan = $this->normalizeKesiapanText(
            $request->input('penempatan')
                ?: $request->input('penempatan_luar_jawa_tengah')
                ?: $request->input('bersedia_ditempatkan')
        );

        $prosesBkhang = $this->normalizeKesiapanText(
            $request->input('proses_bkhang')
                ?: $request->input('background_checking')
                ?: $request->input('bersedia_shift')
        );

        $dapatDipertanggungJawabkan = $this->normalizeKesiapanText(
            $request->input('dapat_dipertanggung_jawabkan')
                ?: $request->input('pernyataan_data_benar')
                ?: $request->input('bersedia_lembur')
        );

        $bersediaTraining = $this->normalizeKesiapanText(
            $request->input('bersedia_training')
                ?: $request->input('bersedia_pelatihan')
        );

        $request->merge([
            'kapan_siap_bekerja' => $kapanSiapBekerja,
            'tanggal_siap_kerja' => $kapanSiapBekerja,

            'ekpetasi_gaji' => $expetasiGaji,
            'gaji_diharapkan' => $expetasiGaji,

            'penempatan' => $penempatan,
            'penempatan_luar_jawa_tengah' => $penempatan,

            'proses_bkhang' => $prosesBkhang,
            'background_checking' => $prosesBkhang,

            'dapat_dipertanggung_jawabkan' => $dapatDipertanggungJawabkan,
            'pernyataan_data_benar' => $dapatDipertanggungJawabkan,

            'bersedia_training' => $bersediaTraining,
            'bersedia_pelatihan' => $bersediaTraining,
        ]);

        $validated = $request->validate([
            'kapan_siap_bekerja' => ['nullable', 'string', 'max:255'],
            'tanggal_siap_kerja' => ['nullable', 'string', 'max:255'],

            'ekpetasi_gaji' => ['nullable', 'numeric', 'min:0', 'max:999999999999999999.99'],
            'gaji_diharapkan' => ['nullable', 'numeric', 'min:0', 'max:999999999999999999.99'],

            'penempatan' => ['nullable', 'string'],
            'penempatan_luar_jawa_tengah' => ['nullable', 'string'],

            'proses_bkhang' => ['nullable', 'string', 'max:255'],
            'background_checking' => ['nullable', 'string', 'max:255'],

            'dapat_dipertanggung_jawabkan' => ['nullable', 'string', 'max:255'],
            'pernyataan_data_benar' => ['nullable', 'string', 'max:255'],

            'bersedia_training' => ['nullable', 'string', 'max:255'],
            'bersedia_pelatihan' => ['nullable', 'string', 'max:255'],
        ], [
            'ekpetasi_gaji.numeric' => 'Ekspetasi gaji harus berupa angka.',
            'gaji_diharapkan.numeric' => 'Ekspetasi gaji harus berupa angka.',
            'ekpetasi_gaji.max' => 'Ekspetasi gaji terlalu besar.',
            'gaji_diharapkan.max' => 'Ekspetasi gaji terlalu besar.',
        ]);

        DB::transaction(function () use ($pelamar, $validated) {
            $kesiapanBekerja = DataKesiapanBekerja::query()
                ->where('data_riwayat_diri_id', $pelamar->id)
                ->first();

            if (!$kesiapanBekerja) {
                $kesiapanBekerja = new DataKesiapanBekerja();
                $kesiapanBekerja->data_riwayat_diri_id = $pelamar->id;
            }

            $table = $kesiapanBekerja->getTable();

            $data = [
                'data_riwayat_diri_id' => $pelamar->id,
                'kapan_siap_bekerja' => $validated['kapan_siap_bekerja']
                    ?? $validated['tanggal_siap_kerja']
                    ?? null,
                'ekpetasi_gaji' => $this->normalizeKesiapanDecimal(
                    $validated['ekpetasi_gaji']
                        ?? $validated['gaji_diharapkan']
                        ?? null
                ),
                'penempatan' => $validated['penempatan']
                    ?? $validated['penempatan_luar_jawa_tengah']
                    ?? null,
                'proses_bkhang' => $validated['proses_bkhang']
                    ?? $validated['background_checking']
                    ?? null,
                'dapat_dipertanggung_jawabkan' => $validated['dapat_dipertanggung_jawabkan']
                    ?? $validated['pernyataan_data_benar']
                    ?? null,
                'bersedia_training' => $validated['bersedia_training']
                    ?? $validated['bersedia_pelatihan']
                    ?? null,
            ];

            $data = collect($data)
                ->filter(fn ($value, $column) => Schema::hasColumn($table, $column))
                ->toArray();

            $kesiapanBekerja->forceFill($data);
            $kesiapanBekerja->save();
        });

        $pelamar = $this->pelamarQuery()
            ->where('token', $token)
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Kesiapan bekerja berhasil diperbarui.',
            'data' => $this->appendPelamarExtraData($pelamar),
        ]);
    }

    public function wilayahProvinces(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->getWilayahOptions(
                table: 'provinces',
                whereColumn: null,
                whereValue: null
            ),
        ]);
    }

    public function wilayahRegencies(string $province_code): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->getWilayahOptions(
                table: 'regencies',
                whereColumn: 'province_code',
                whereValue: $province_code
            ),
        ]);
    }

    public function wilayahDistricts(string $regency_code): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->getWilayahOptions(
                table: 'districts',
                whereColumn: 'regency_code',
                whereValue: $regency_code
            ),
        ]);
    }

    public function wilayahVillages(string $district_code): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->getWilayahOptions(
                table: 'villages',
                whereColumn: 'district_code',
                whereValue: $district_code
            ),
        ]);
    }

    private function syncSosialMedia(DataRiwayatDiri $pelamar, array $items = []): void
    {
        if (!Schema::hasTable('sosial_media')) {
            return;
        }

        if (
            !Schema::hasColumn('sosial_media', 'data_riwayat_diri_id') ||
            !Schema::hasColumn('sosial_media', 'platform') ||
            !Schema::hasColumn('sosial_media', 'nama_account')
        ) {
            return;
        }

        $normalizedItems = collect($items)
            ->map(function ($item) {
                $platform = trim((string) ($item['platform'] ?? ''));

                $namaAccount = trim((string) (
                    $item['nama_akun']
                    ?? $item['nama_account']
                    ?? ''
                ));

                return [
                    'id' => $item['id'] ?? null,
                    'platform' => $platform,
                    'nama_account' => $namaAccount,
                ];
            })
            ->filter(function ($item) {
                return $item['platform'] !== '' || $item['nama_account'] !== '';
            })
            ->unique(function ($item) {
                return strtolower($item['platform']) . '|' . strtolower($item['nama_account']);
            })
            ->values();

        $activeRows = SosialMedia::query()
            ->where('data_riwayat_diri_id', $pelamar->id)
            ->orderBy('created_at')
            ->get();

        $keptIds = [];

        foreach ($normalizedItems as $index => $item) {
            $row = null;

            if (!empty($item['id']) && Schema::hasColumn('sosial_media', 'id')) {
                $row = SosialMedia::query()
                    ->where('id', $item['id'])
                    ->where('data_riwayat_diri_id', $pelamar->id)
                    ->first();
            }

            if (!$row && isset($activeRows[$index])) {
                $candidateRow = $activeRows[$index];

                if (!in_array($candidateRow->id, $keptIds, true)) {
                    $row = $candidateRow;
                }
            }

            if (!$row) {
                $row = new SosialMedia();
                $row->data_riwayat_diri_id = $pelamar->id;
            }

            $row->platform = $item['platform'];
            $row->nama_account = $item['nama_account'];
            $row->save();

            $keptIds[] = $row->id;
        }

        SosialMedia::query()
            ->where('data_riwayat_diri_id', $pelamar->id)
            ->when(count($keptIds) > 0, function ($query) use ($keptIds) {
                $query->whereNotIn('id', $keptIds);
            })
            ->delete();
    }

    private function syncSaudaraKandung(
        DataRiwayatDiri $pelamar,
        DataRiwayatKeluarga $riwayatKeluarga,
        array $items = []
    ): void {
        if (!Schema::hasTable('data_saudara_kandung')) {
            return;
        }

        $normalizedItems = collect($items)
            ->map(function ($item) {
                return [
                    'id' => $item['id'] ?? null,
                    'nama' => trim((string) ($item['nama'] ?? $item['nama_saudara_kandung'] ?? '')),
                    'jenis_kelamin' => trim((string) ($item['jenis_kelamin'] ?? '')),
                    'hubungan' => trim((string) ($item['hubungan'] ?? '')),
                    'pekerjaan' => trim((string) ($item['pekerjaan'] ?? '')),
                    'no_hp' => trim((string) ($item['no_hp'] ?? '')),
                    'alamat' => trim((string) ($item['alamat'] ?? '')),
                ];
            })
            ->filter(function ($item) {
                return $item['nama'] !== '' ||
                    $item['jenis_kelamin'] !== '' ||
                    $item['hubungan'] !== '' ||
                    $item['pekerjaan'] !== '' ||
                    $item['no_hp'] !== '' ||
                    $item['alamat'] !== '';
            })
            ->values();

        $activeRows = DataSaudaraKandung::query()
            ->where('data_riwayat_diri_id', $pelamar->id)
            ->orderBy('created_at')
            ->get();

        $keptIds = [];

        foreach ($normalizedItems as $index => $item) {
            $row = null;

            if (!empty($item['id'])) {
                $row = DataSaudaraKandung::query()
                    ->where('id', $item['id'])
                    ->where('data_riwayat_diri_id', $pelamar->id)
                    ->first();
            }

            if (!$row && isset($activeRows[$index])) {
                $candidateRow = $activeRows[$index];

                if (!in_array($candidateRow->id, $keptIds, true)) {
                    $row = $candidateRow;
                }
            }

            if (!$row) {
                $row = new DataSaudaraKandung();
                $row->data_riwayat_diri_id = $pelamar->id;
                $row->data_riwayat_keluarga_id = $riwayatKeluarga->id;
            }

            $table = $row->getTable();

            $data = [
                'data_riwayat_diri_id' => $pelamar->id,
                'data_riwayat_keluarga_id' => $riwayatKeluarga->id,
                'nama_saudara_kandung' => $item['nama'],
                'jenis_kelamin' => $item['jenis_kelamin'],
                'hubungan' => $item['hubungan'],
                'pekerjaan' => $item['pekerjaan'],
                'no_hp' => $item['no_hp'],
                'alamat' => $item['alamat'],
            ];

            $data = collect($data)
                ->filter(fn ($value, $column) => Schema::hasColumn($table, $column))
                ->toArray();

            $row->forceFill($data);
            $row->save();

            $keptIds[] = $row->id;
        }

        DataSaudaraKandung::query()
            ->where('data_riwayat_diri_id', $pelamar->id)
            ->when(count($keptIds) > 0, function ($query) use ($keptIds) {
                $query->whereNotIn('id', $keptIds);
            })
            ->delete();
    }

    private function syncSaudaraIpar(
        DataRiwayatDiri $pelamar,
        DataRiwayatKeluarga $riwayatKeluarga,
        array $items = []
    ): void {
        if (!Schema::hasTable('data_saudara_ipar')) {
            return;
        }

        $normalizedItems = collect($items)
            ->map(function ($item) {
                return [
                    'id' => $item['id'] ?? null,
                    'nama' => trim((string) ($item['nama'] ?? $item['nama_saudara_ipar'] ?? '')),
                    'jenis_kelamin' => trim((string) ($item['jenis_kelamin'] ?? '')),
                    'hubungan' => trim((string) ($item['hubungan'] ?? '')),
                    'pekerjaan' => trim((string) ($item['pekerjaan'] ?? '')),
                    'no_hp' => trim((string) ($item['no_hp'] ?? '')),
                    'alamat' => trim((string) ($item['alamat'] ?? '')),
                ];
            })
            ->filter(function ($item) {
                return $item['nama'] !== '' ||
                    $item['jenis_kelamin'] !== '' ||
                    $item['hubungan'] !== '' ||
                    $item['pekerjaan'] !== '' ||
                    $item['no_hp'] !== '' ||
                    $item['alamat'] !== '';
            })
            ->values();

        $activeRows = DataSaudaraIpar::query()
            ->where('data_riwayat_diri_id', $pelamar->id)
            ->orderBy('created_at')
            ->get();

        $keptIds = [];

        foreach ($normalizedItems as $index => $item) {
            $row = null;

            if (!empty($item['id'])) {
                $row = DataSaudaraIpar::query()
                    ->where('id', $item['id'])
                    ->where('data_riwayat_diri_id', $pelamar->id)
                    ->first();
            }

            if (!$row && isset($activeRows[$index])) {
                $candidateRow = $activeRows[$index];

                if (!in_array($candidateRow->id, $keptIds, true)) {
                    $row = $candidateRow;
                }
            }

            if (!$row) {
                $row = new DataSaudaraIpar();
                $row->data_riwayat_diri_id = $pelamar->id;
                $row->data_riwayat_keluarga_id = $riwayatKeluarga->id;
            }

            $table = $row->getTable();

            $data = [
                'data_riwayat_diri_id' => $pelamar->id,
                'data_riwayat_keluarga_id' => $riwayatKeluarga->id,
                'nama_saudara_ipar' => $item['nama'],
                'jenis_kelamin' => $item['jenis_kelamin'],
                'hubungan' => $item['hubungan'],
                'pekerjaan' => $item['pekerjaan'],
                'no_hp' => $item['no_hp'],
                'alamat' => $item['alamat'],
            ];

            $data = collect($data)
                ->filter(fn ($value, $column) => Schema::hasColumn($table, $column))
                ->toArray();

            $row->forceFill($data);
            $row->save();

            $keptIds[] = $row->id;
        }

        DataSaudaraIpar::query()
            ->where('data_riwayat_diri_id', $pelamar->id)
            ->when(count($keptIds) > 0, function ($query) use ($keptIds) {
                $query->whereNotIn('id', $keptIds);
            })
            ->delete();
    }


private function appendPelamarExtraData(?DataRiwayatDiri $pelamar): ?DataRiwayatDiri
    {
        if (!$pelamar) {
            return null;
        }

        $this->appendWilayahLabels($pelamar);
        $this->appendRiwayatKeluargaData($pelamar);
        $this->appendRiwayatKesehatanData($pelamar);
        $this->appendRiwayatPekerjaanData($pelamar);
        $this->appendKesiapanBekerjaData($pelamar);

        return $pelamar;
    }

    private function appendRiwayatKeluargaData(DataRiwayatDiri $pelamar): DataRiwayatDiri
    {
        $pelamar->loadMissing([
            'riwayatKeluarga',
            'saudaraKandung',
            'saudaraIpar',
        ]);

        $keluarga = $pelamar->riwayatKeluarga;

        if ($keluarga) {
            foreach ([
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
                'kerabat_bekerja_diinstansi',
                'hubungan_kerabat_instansi',
                'kontak_darurat',
                'tlpn_darurat',
            ] as $column) {
                if ($keluarga->{$column} !== null) {
                    $pelamar->setAttribute($column, $keluarga->{$column});
                }
            }

            if (
                empty($pelamar->hubungan_kerabat_instansi) &&
                !empty($keluarga->kerabat_bekerja_diinstansi)
            ) {
                $pelamar->setAttribute(
                    'hubungan_kerabat_instansi',
                    $this->normalizeArrayInput($keluarga->kerabat_bekerja_diinstansi)
                );
            }

            if (
                empty($pelamar->kontak_darurat) &&
                !empty($keluarga->tlpn_darurat)
            ) {
                $pelamar->setAttribute('kontak_darurat', [[
                    'nama' => '',
                    'status' => '',
                    'nomor' => $keluarga->tlpn_darurat,
                ]]);
            }
        }

        $pelamar->setAttribute(
            'saudara_kandung',
            $pelamar->saudaraKandung
                ? $pelamar->saudaraKandung->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'nama' => $item->nama_saudara_kandung ?? '',
                        'jenis_kelamin' => $item->jenis_kelamin ?? '',
                        'hubungan' => $item->hubungan ?? '',
                        'pekerjaan' => $item->pekerjaan ?? '',
                        'no_hp' => $item->no_hp ?? '',
                        'alamat' => $item->alamat ?? '',
                    ];
                })->values()->all()
                : []
        );

        $pelamar->setAttribute(
            'saudara_ipar',
            $pelamar->saudaraIpar
                ? $pelamar->saudaraIpar->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'nama' => $item->nama_saudara_ipar ?? '',
                        'jenis_kelamin' => $item->jenis_kelamin ?? '',
                        'hubungan' => $item->hubungan ?? '',
                        'pekerjaan' => $item->pekerjaan ?? '',
                        'no_hp' => $item->no_hp ?? '',
                        'alamat' => $item->alamat ?? '',
                    ];
                })->values()->all()
                : []
        );

        return $pelamar;
    }

    private function appendRiwayatKesehatanData(DataRiwayatDiri $pelamar): DataRiwayatDiri
    {
        $pelamar->loadMissing([
            'riwayatKesehatan',
            'riwayatKesehatan.opsiKacamata',
        ]);

        $kesehatan = $pelamar->riwayatKesehatan;

        if (!$kesehatan) {
            return $pelamar;
        }

        foreach ([
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
        ] as $column) {
            if ($kesehatan->{$column} !== null) {
                $pelamar->setAttribute($column, $kesehatan->{$column});
            }
        }

        $pelamar->setAttribute(
            'opsi_kacamata_label',
            $kesehatan->opsiKacamata->opsi ?? null
        );

        $pelamar->setAttribute(
            'alat_bantu_pendengaran',
            $kesehatan->alat_bantu_dengar ?? null
        );

        $pelamar->setAttribute(
            'tangan_dominan',
            $kesehatan->menulis_dengan_tangan ?? null
        );

        $pelamar->setAttribute(
            'tangan_gemetar',
            $kesehatan->sering_gemetar ?? null
        );

        $pelamar->setAttribute(
            'tangan_berkeringat',
            $kesehatan->tangan_sering_berkeringat ?? null
        );

        $pelamar->setAttribute(
            'riwayat_penyakit_menular',
            $kesehatan->penyakit_menular ?? null
        );

        $pelamar->setAttribute(
            'memiliki_alergi',
            $kesehatan->punya_alergi ?? null
        );

        $pelamar->setAttribute(
            'alergi',
            $kesehatan->nama_alergi ?? null
        );

        return $pelamar;
    }


    private function appendRiwayatPekerjaanData(DataRiwayatDiri $pelamar): DataRiwayatDiri
    {
        $pelamar->loadMissing([
            'riwayatPekerjaan',
        ]);

        $pekerjaan = $pelamar->riwayatPekerjaan;

        if (!$pekerjaan) {
            return $pelamar;
        }

        foreach ([
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
        ] as $column) {
            if ($pekerjaan->{$column} !== null) {
                $pelamar->setAttribute($column, $pekerjaan->{$column});
            }
        }

        if (empty($pelamar->posisi_pekerjaan) && !empty($pekerjaan->posisi_pekerjaan_terakhir)) {
            $pelamar->setAttribute('posisi_pekerjaan', $pekerjaan->posisi_pekerjaan_terakhir);
        }

        if (empty($pelamar->posisi_pekerjaan_terakhir) && !empty($pekerjaan->posisi_pekerjaan)) {
            $pelamar->setAttribute('posisi_pekerjaan_terakhir', $pekerjaan->posisi_pekerjaan);
        }

        if (empty($pelamar->tahun_mulai_bekerja) && !empty($pekerjaan->periode_kerja_awal)) {
            $pelamar->setAttribute('tahun_mulai_bekerja', $this->dateToYear($pekerjaan->periode_kerja_awal));
        }

        if (empty($pelamar->tahun_selesai_bekerja) && !empty($pekerjaan->periode_kerja_akhir)) {
            $pelamar->setAttribute('tahun_selesai_bekerja', $this->dateToYear($pekerjaan->periode_kerja_akhir));
        }

        if (empty($pelamar->lama_bekerja)) {
            $pelamar->setAttribute(
                'lama_bekerja',
                $this->calculateLamaBekerjaFromYear(
                    $pelamar->tahun_mulai_bekerja ?? null,
                    $pelamar->tahun_selesai_bekerja ?? null
                )
            );
        }

        return $pelamar;
    }


    private function appendKesiapanBekerjaData(DataRiwayatDiri $pelamar): DataRiwayatDiri
    {
        if (!Schema::hasTable('data_kesiapan_bekerja')) {
            return $pelamar;
        }

        $kesiapan = DataKesiapanBekerja::query()
            ->where('data_riwayat_diri_id', $pelamar->id)
            ->first();

        if (!$kesiapan) {
            return $pelamar;
        }

        foreach ([
            'kapan_siap_bekerja',
            'ekpetasi_gaji',
            'penempatan',
            'proses_bkhang',
            'dapat_dipertanggung_jawabkan',
            'bersedia_training',
        ] as $column) {
            if ($kesiapan->{$column} !== null) {
                $pelamar->setAttribute($column, $kesiapan->{$column});
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Alias untuk kompatibilitas form React sebelumnya
        |--------------------------------------------------------------------------
        */
        $pelamar->setAttribute('tanggal_siap_kerja', $kesiapan->kapan_siap_bekerja);
        $pelamar->setAttribute('gaji_diharapkan', $kesiapan->ekpetasi_gaji);
        $pelamar->setAttribute('penempatan_luar_jawa_tengah', $this->normalizeArrayInput($kesiapan->penempatan));
        $pelamar->setAttribute('background_checking', $kesiapan->proses_bkhang);
        $pelamar->setAttribute('pernyataan_data_benar', $kesiapan->dapat_dipertanggung_jawabkan);
        $pelamar->setAttribute('bersedia_pelatihan', $kesiapan->bersedia_training);

        return $pelamar;
    }

    private function normalizeStrAktif($value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $normalized = $this->normalizeForMasterSearch($value);

        return match ($normalized) {
            'active', 'aktif', 'ya', 'yes', 'y', 'true', '1', 'ada' => 'active',
            'nonactive', 'inactive', 'nonaktif', 'tidak', 'no', 'n', 'false', '0', 'tidakada', 'none' => 'non_active',
            default => null,
        };
    }

    private function getStrAktifOptions(): array
    {
        return [
            [
                'id' => 'active',
                'value' => 'active',
                'label' => 'Ya',
            ],
            [
                'id' => 'non_active',
                'value' => 'non_active',
                'label' => 'Tidak',
            ],
        ];
    }

    private function getStrAktifAllowedValues(): array
    {
        if (config('database.default') !== 'pgsql') {
            return [];
        }

        try {
            $row = DB::selectOne(
                "SELECT pg_get_constraintdef(oid) AS definition
                 FROM pg_constraint
                 WHERE conname = ?
                 LIMIT 1",
                ['data_riwayat_diri_str_aktif_check']
            );

            $definition = (string) ($row->definition ?? '');

            if ($definition === '') {
                return [];
            }

            preg_match_all("/'((?:[^']|'')+)'(?:::|,|\)|\])?/", $definition, $matches);

            return collect($matches[1] ?? [])
                ->map(fn ($value) => str_replace("''", "'", trim((string) $value)))
                ->filter(fn ($value) => $value !== '')
                ->unique()
                ->values()
                ->all();
        } catch (\Throwable $error) {
            return [];
        }
    }

    private function findAllowedStrAktifValue(string $value, array $allowedValues): ?string
    {
        $normalizedValue = $this->normalizeForMasterSearch($value);

        foreach ($allowedValues as $allowedValue) {
            if ($this->normalizeForMasterSearch($allowedValue) === $normalizedValue) {
                return (string) $allowedValue;
            }
        }

        return null;
    }

    private function normalizeArrayInput($value): array
    {
        if (is_array($value)) {
            return collect($value)
                ->map(fn ($item) => trim((string) $item))
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $this->normalizeArrayInput($decoded);
            }

            return collect(explode(',', $value))
                ->map(fn ($item) => trim((string) $item))
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        return [];
    }

    private function normalizeKontakDarurat(array $items = []): array
    {
        return collect($items)
            ->map(function ($item) {
                return [
                    'nama' => trim((string) ($item['nama'] ?? '')),
                    'status' => trim((string) ($item['status'] ?? '')),
                    'nomor' => trim((string) ($item['nomor'] ?? '')),
                ];
            })
            ->filter(function ($item) {
                return $item['nama'] !== '' ||
                    $item['status'] !== '' ||
                    $item['nomor'] !== '';
            })
            ->values()
            ->all();
    }

    private function getWilayahOptions(
        string $table,
        ?string $whereColumn = null,
        ?string $whereValue = null
    ): array {
        if (
            !Schema::hasTable($table) ||
            !Schema::hasColumn($table, 'code') ||
            !Schema::hasColumn($table, 'name')
        ) {
            return [];
        }

        $query = DB::table($table)
            ->select('code', 'name')
            ->whereNotNull('code')
            ->whereNotNull('name')
            ->orderBy('name');

        if ($whereColumn && Schema::hasColumn($table, $whereColumn)) {
            $query->where($whereColumn, $whereValue);
        }

        return $query->get()
            ->map(function ($row) {
                return [
                    'id' => (string) $row->code,
                    'value' => (string) $row->code,
                    'label' => (string) $row->name,
                    'code' => (string) $row->code,
                    'name' => (string) $row->name,
                ];
            })
            ->filter(function ($row) {
                return trim($row['value']) !== '' && trim($row['label']) !== '';
            })
            ->values()
            ->all();
    }

    private function validateWilayahBertingkat(
        ?string $provinsiCode,
        ?string $kabupatenCode,
        ?string $kecamatanCode,
        ?string $kelurahanCode
    ): ?JsonResponse {
        if ($provinsiCode && !$this->wilayahCodeExists('provinces', 'code', $provinsiCode)) {
            return $this->masterError('provinsi_id', 'Provinsi yang dipilih tidak ditemukan.');
        }

        if ($kabupatenCode) {
            if (!$provinsiCode) {
                return $this->masterError('kabupaten_id', 'Pilih provinsi terlebih dahulu.');
            }

            if (!$this->wilayahCodeExists('regencies', 'code', $kabupatenCode, 'province_code', $provinsiCode)) {
                return $this->masterError('kabupaten_id', 'Kabupaten / Kota yang dipilih tidak sesuai dengan provinsi.');
            }
        }

        if ($kecamatanCode) {
            if (!$kabupatenCode) {
                return $this->masterError('kecamatan_id', 'Pilih kabupaten / kota terlebih dahulu.');
            }

            if (!$this->wilayahCodeExists('districts', 'code', $kecamatanCode, 'regency_code', $kabupatenCode)) {
                return $this->masterError('kecamatan_id', 'Kecamatan yang dipilih tidak sesuai dengan kabupaten / kota.');
            }
        }

        if ($kelurahanCode) {
            if (!$kecamatanCode) {
                return $this->masterError('kelurahan_id', 'Pilih kecamatan terlebih dahulu.');
            }

            if (!$this->wilayahCodeExists('villages', 'code', $kelurahanCode, 'district_code', $kecamatanCode)) {
                return $this->masterError('kelurahan_id', 'Kelurahan / Desa yang dipilih tidak sesuai dengan kecamatan.');
            }
        }

        return null;
    }

    private function wilayahCodeExists(
        string $table,
        string $codeColumn,
        string $codeValue,
        ?string $parentColumn = null,
        ?string $parentValue = null
    ): bool {
        if (
            !Schema::hasTable($table) ||
            !Schema::hasColumn($table, $codeColumn)
        ) {
            return false;
        }

        $query = DB::table($table)
            ->where($codeColumn, $codeValue);

        if ($parentColumn && Schema::hasColumn($table, $parentColumn)) {
            $query->where($parentColumn, $parentValue);
        }

        return $query->exists();
    }

    private function appendWilayahLabels(?DataRiwayatDiri $pelamar): ?DataRiwayatDiri
    {
        if (!$pelamar) {
            return null;
        }

        $pelamar->setAttribute(
            'provinsi_label',
            $this->getWilayahName('provinces', $pelamar->provinsi_id ?? null)
        );

        $pelamar->setAttribute(
            'kabupaten_label',
            $this->getWilayahName('regencies', $pelamar->kabupaten_id ?? null)
        );

        $pelamar->setAttribute(
            'kecamatan_label',
            $this->getWilayahName('districts', $pelamar->kecamatan_id ?? null)
        );

        $pelamar->setAttribute(
            'kelurahan_label',
            $this->getWilayahName('villages', $pelamar->kelurahan_id ?? null)
        );

        return $pelamar;
    }

    private function getWilayahName(string $table, ?string $code): ?string
    {
        if (!$code) {
            return null;
        }

        if (
            !Schema::hasTable($table) ||
            !Schema::hasColumn($table, 'code') ||
            !Schema::hasColumn($table, 'name')
        ) {
            return null;
        }

        $name = DB::table($table)
            ->where('code', $code)
            ->value('name');

        return $name ? (string) $name : null;
    }

    private function getHardcodedSosialMediaPlatforms(): array
    {
        $platforms = [
            'Instagram',
            'Facebook',
            'TikTok',
            'LinkedIn',
            'X / Twitter',
            'YouTube',
            'Telegram',
            'WhatsApp',
            'Lainnya',
        ];

        return collect($platforms)
            ->map(function ($platform) {
                return [
                    'id' => $platform,
                    'value' => $platform,
                    'label' => $platform,
                ];
            })
            ->values()
            ->all();
    }

    private function getMasterOptions(array $tables, array $labelColumns): array
    {
        foreach ($tables as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'id')) {
                continue;
            }

            foreach ($labelColumns as $labelColumn) {
                if (!Schema::hasColumn($table, $labelColumn)) {
                    continue;
                }

                $query = DB::table($table)
                    ->select('id', $labelColumn)
                    ->whereNotNull('id')
                    ->whereNotNull($labelColumn)
                    ->orderBy($labelColumn);

                if (Schema::hasColumn($table, 'deleted_at')) {
                    $query->whereNull('deleted_at');
                }

                $rows = $query->get()
                    ->map(function ($row) use ($labelColumn) {
                        return [
                            'id' => (string) $row->id,
                            'value' => (string) $row->id,
                            'label' => (string) $row->{$labelColumn},
                        ];
                    })
                    ->filter(fn ($row) => trim($row['label']) !== '')
                    ->values()
                    ->all();

                if (count($rows) > 0) {
                    return $rows;
                }
            }
        }

        return [];
    }

    private function getColumnOptions(string $table, string $column, array $fallbackValues = []): array
    {
        $values = [];

        if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
            $query = DB::table($table)
                ->select($column)
                ->whereNotNull($column)
                ->distinct()
                ->orderBy($column);

            if (Schema::hasColumn($table, 'deleted_at')) {
                $query->whereNull('deleted_at');
            }

            $values = $query->pluck($column)
                ->map(fn ($value) => trim((string) $value))
                ->filter()
                ->values()
                ->all();
        }

        $values = array_values(array_unique(array_merge($values, $fallbackValues)));

        return array_map(function ($value) {
            return [
                'id' => $value,
                'value' => $value,
                'label' => $value,
            ];
        }, $values);
    }

    private function getPosisiOptions(): array
    {
        foreach ($this->tablesFor([\App\Models\Posisi::class], [
            'posisi',
            'data_posisi',
            'master_data_posisi',
            'master_posisi',
        ]) as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'id')) {
                continue;
            }

            $labelColumn = null;

            foreach ([
                'nama_posisi',
                'posisi',
                'nama_posisi_dilamar',
                'posisi_dilamar',
                'jabatan',
                'nama_jabatan',
                'nama',
            ] as $candidateColumn) {
                if (Schema::hasColumn($table, $candidateColumn)) {
                    $labelColumn = $candidateColumn;
                    break;
                }
            }

            if (!$labelColumn) {
                continue;
            }

            $select = ['id', $labelColumn];

            if (Schema::hasColumn($table, 'str_aktif')) {
                $select[] = 'str_aktif';
            }

            $query = DB::table($table)
                ->select($select)
                ->whereNotNull('id')
                ->whereNotNull($labelColumn)
                ->orderBy($labelColumn);

            if (Schema::hasColumn($table, 'deleted_at')) {
                $query->whereNull('deleted_at');
            }

            $rows = $query->get()
                ->map(function ($row) use ($labelColumn) {
                    return [
                        'id' => (string) $row->id,
                        'value' => (string) $row->id,
                        'label' => (string) $row->{$labelColumn},
                        'str_aktif' => (string) ($row->str_aktif ?? ''),
                    ];
                })
                ->filter(fn ($row) => trim($row['label']) !== '')
                ->values()
                ->all();

            if (count($rows) > 0) {
                return $rows;
            }
        }

        return [];
    }

    private function masterError(string $field, string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => [
                $field => [$message],
            ],
        ], 422);
    }

    private function findPendidikanId(?string $value): ?string
    {
        return $this->findExactMasterId(
            'pendidikan',
            'pendidikan',
            $value,
            [
                'smasmk' => ['SMA / SMK', 'SMA/SMK', 'SMA SMK', 'SMA', 'SMK', 'SLTA'],
                'sma' => ['SMA', 'SMA / SMK', 'SMA/SMK', 'SMA SMK', 'SLTA'],
                'smk' => ['SMK', 'SMA / SMK', 'SMA/SMK', 'SMA SMK', 'SLTA'],
                'd1' => ['D1', 'Diploma 1', 'Diploma I'],
                'd2' => ['D2', 'Diploma 2', 'Diploma II'],
                'd3' => ['D3', 'Diploma 3', 'Diploma III'],
                'd4' => ['D4', 'Diploma 4', 'Diploma IV'],
                's1' => ['S1', 'Sarjana', 'Strata 1', 'Strata I'],
                's2' => ['S2', 'Magister', 'Strata 2', 'Strata II'],
                's3' => ['S3', 'Doktor', 'Strata 3', 'Strata III'],
            ]
        );
    }

    private function findStatusPernikahanId(?string $value): ?string
    {
        return $this->findExactMasterId(
            'status_pernikahan',
            'status_pernikahan',
            $value,
            [
                'belumkawin' => ['Belum Kawin', 'Belum Menikah', 'Tidak Menikah', 'Lajang', 'Single'],
                'belummenikah' => ['Belum Kawin', 'Belum Menikah', 'Tidak Menikah', 'Lajang', 'Single'],
                'lajang' => ['Lajang', 'Belum Kawin', 'Belum Menikah', 'Tidak Menikah', 'Single'],
                'single' => ['Single', 'Lajang', 'Belum Kawin', 'Belum Menikah'],
                'kawin' => ['Kawin', 'Menikah'],
                'menikah' => ['Menikah', 'Kawin'],
                'cerai' => ['Cerai', 'Cerai Hidup', 'Cerai Mati'],
                'ceraihidup' => ['Cerai Hidup', 'Cerai'],
                'ceraimati' => ['Cerai Mati', 'Cerai'],
            ]
        );
    }

    private function findExactMasterId(
        string $table,
        string $labelColumn,
        ?string $value,
        array $aliases = []
    ): ?string {
        if (!$value) {
            return null;
        }

        $value = trim($value);

        if (
            !Schema::hasTable($table) ||
            !Schema::hasColumn($table, 'id') ||
            !Schema::hasColumn($table, $labelColumn)
        ) {
            return null;
        }

        $baseQuery = DB::table($table);

        if (Schema::hasColumn($table, 'deleted_at')) {
            $baseQuery->whereNull('deleted_at');
        }

        if (Str::isUuid($value)) {
            $id = (clone $baseQuery)
                ->where('id', $value)
                ->value('id');

            if ($id) {
                return (string) $id;
            }
        }

        $candidateLabels = $this->masterValueCandidates($value, $aliases);

        $candidateKeys = collect($candidateLabels)
            ->map(fn ($candidate) => $this->normalizeForMasterSearch($candidate))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $rows = (clone $baseQuery)
            ->select('id', $labelColumn)
            ->whereNotNull($labelColumn)
            ->get();

        foreach ($rows as $row) {
            $label = (string) ($row->{$labelColumn} ?? '');
            $labelKey = $this->normalizeForMasterSearch($label);

            if (in_array($labelKey, $candidateKeys, true)) {
                return (string) $row->id;
            }
        }

        return null;
    }

    private function masterValueCandidates(?string $value, array $aliases = []): array
    {
        if (!$value) {
            return [];
        }

        $value = trim($value);
        $key = $this->normalizeForMasterSearch($value);

        $candidates = $aliases[$key] ?? [];
        $candidates[] = $value;

        return array_values(array_unique(array_filter(array_map('trim', $candidates))));
    }

    private function normalizeForMasterSearch(string $value): string
    {
        return preg_replace('/[^a-z0-9]+/', '', Str::lower($value)) ?? '';
    }

    private function isPosisiWajibStr(?string $posisiId): bool
    {
        if (!$posisiId || !Str::isUuid($posisiId)) {
            return false;
        }

        foreach ($this->tablesFor([\App\Models\Posisi::class], [
            'posisi',
            'data_posisi',
            'master_data_posisi',
            'master_posisi',
        ]) as $table) {
            if (
                !Schema::hasTable($table) ||
                !Schema::hasColumn($table, 'id') ||
                !Schema::hasColumn($table, 'str_aktif')
            ) {
                continue;
            }

            $query = DB::table($table)->where('id', $posisiId);

            if (Schema::hasColumn($table, 'deleted_at')) {
                $query->whereNull('deleted_at');
            }

            $value = $query->value('str_aktif');

            if ($value === null) {
                continue;
            }

            return $this->normalizeForMasterSearch((string) $value) === 'active';
        }

        return false;
    }

    private function getPosisiIdFromRequestOrPelamar(?string $value, DataRiwayatDiri $pelamar): ?string
    {
        $value = trim((string) ($value ?: ''));

        if ($value !== '') {
            $posisiId = $this->findMasterId(
                $this->tablesFor([\App\Models\Posisi::class], [
                    'posisi',
                    'data_posisi',
                    'master_data_posisi',
                    'master_posisi',
                ]),
                [
                    'nama_posisi',
                    'posisi',
                    'nama_posisi_dilamar',
                    'posisi_dilamar',
                    'jabatan',
                    'nama_jabatan',
                    'nama',
                ],
                $value
            );

            if ($posisiId) {
                return $posisiId;
            }
        }

        $fallbackId = $pelamar->posisi_dilamar
            ?? $pelamar->posisi_yang_dilamar
            ?? null;

        return $fallbackId ? (string) $fallbackId : null;
    }


    private function resolvePosisiDilamarIdForKesiapan(?string $value = null, ?DataRiwayatDiri $pelamar = null): ?string
    {
        $value = trim((string) ($value ?: ''));

        if ($value !== '') {
            foreach ($this->tablesFor([\App\Models\Posisi::class], [
                'posisi',
                'data_posisi',
                'master_data_posisi',
                'master_posisi',
            ]) as $table) {
                if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'id')) {
                    continue;
                }

                $query = DB::table($table)
                    ->where('id', $value);

                if (Schema::hasColumn($table, 'deleted_at')) {
                    $query->whereNull('deleted_at');
                }

                if ($query->exists()) {
                    return $value;
                }
            }

            $posisiId = $this->findMasterId(
                $this->tablesFor([\App\Models\Posisi::class], [
                    'posisi',
                    'data_posisi',
                    'master_data_posisi',
                    'master_posisi',
                ]),
                [
                    'nama_posisi',
                    'posisi',
                    'nama_posisi_dilamar',
                    'posisi_dilamar',
                    'jabatan',
                    'nama_jabatan',
                    'nama',
                    'kode_posisi',
                ],
                $value
            );

            if ($posisiId) {
                return $posisiId;
            }
        }

        $fallbackId = $pelamar
            ? (
                $pelamar->posisi_dilamar
                ?? $pelamar->posisi_yang_dilamar
                ?? null
            )
            : null;

        return $fallbackId ? (string) $fallbackId : null;
    }

    
    private function resolvePosisiDilamarForKesiapan(?string $value = null, ?DataRiwayatDiri $pelamar = null): ?string
    {
        return $this->resolvePosisiDilamarIdForKesiapan($value, $pelamar);
    }

private function getPosisiLabelById(?string $posisiId): ?string
    {
        if (!$posisiId) {
            return null;
        }

        foreach ($this->tablesFor([\App\Models\Posisi::class], [
            'posisi',
            'data_posisi',
            'master_data_posisi',
            'master_posisi',
        ]) as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'id')) {
                continue;
            }

            foreach ([
                'nama_posisi',
                'posisi',
                'nama_posisi_dilamar',
                'posisi_dilamar',
                'jabatan',
                'nama_jabatan',
                'nama',
            ] as $column) {
                if (!Schema::hasColumn($table, $column)) {
                    continue;
                }

                $query = DB::table($table)
                    ->where('id', $posisiId);

                if (Schema::hasColumn($table, 'deleted_at')) {
                    $query->whereNull('deleted_at');
                }

                $label = $query->value($column);

                if ($label !== null && trim((string) $label) !== '') {
                    return (string) $label;
                }
            }
        }

        return null;
    }


    private function normalizeKesiapanText($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (is_array($value)) {
            $value = collect($value)
                ->flatten()
                ->map(fn ($item) => trim((string) $item))
                ->filter()
                ->unique()
                ->values()
                ->implode(', ');

            return $value !== '' ? $value : null;
        }

        $stringValue = trim((string) $value);

        if ($stringValue === '') {
            return null;
        }

        $decoded = json_decode($stringValue, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $this->normalizeKesiapanText($decoded);
        }

        return $stringValue;
    }

    private function normalizeKesiapanDecimal($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $value = preg_replace('/[^0-9,.]/', '', $value);

        if ($value === '' || $value === '.' || $value === ',') {
            return null;
        }

        $hasComma = str_contains($value, ',');
        $hasDot = str_contains($value, '.');

        if ($hasComma && $hasDot) {
            $lastComma = strrpos($value, ',');
            $lastDot = strrpos($value, '.');

            if ($lastComma > $lastDot) {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } else {
                $value = str_replace(',', '', $value);
            }
        } elseif ($hasComma) {
            $parts = explode(',', $value);
            $lastPart = end($parts);

            if (strlen($lastPart) <= 2 && count($parts) === 2) {
                $value = str_replace(',', '.', $value);
            } else {
                $value = str_replace(',', '', $value);
            }
        } elseif ($hasDot) {
            $parts = explode('.', $value);
            $lastPart = end($parts);

            if (!(strlen($lastPart) <= 2 && count($parts) === 2)) {
                $value = str_replace('.', '', $value);
            }
        }

        if (!is_numeric($value)) {
            return null;
        }

        $maxValue = 999999999999999999.99;
        $numericValue = (float) $value;

        if ($numericValue > $maxValue) {
            $numericValue = $maxValue;
        }

        return number_format($numericValue, 2, '.', '');
    }

    private function normalizeKesiapanDate($value): ?string
    {
        return $this->normalizeKesiapanText($value);
    }

    private function normalizeYearValue($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $year = preg_replace('/\D/', '', (string) $value);
        $year = substr($year, 0, 4);

        return strlen($year) === 4 ? $year : null;
    }

    private function yearToDate(?string $year): ?string
    {
        if (!$year || !preg_match('/^\d{4}$/', $year)) {
            return null;
        }

        return $year . '-01-01';
    }

    private function calculateLamaBekerjaFromYear(?string $tahunMulai, ?string $tahunSelesai): ?string
    {
        if (
            !$tahunMulai ||
            !$tahunSelesai ||
            !preg_match('/^\d{4}$/', $tahunMulai) ||
            !preg_match('/^\d{4}$/', $tahunSelesai)
        ) {
            return null;
        }

        $mulai = (int) $tahunMulai;
        $selesai = (int) $tahunSelesai;

        if ($selesai < $mulai) {
            return null;
        }

        return (string) ($selesai - $mulai);
    }

        private function normalizeYearToDate(?string $value): ?string
    {
        return $this->normalizeRiwayatPekerjaanDate($value);
    }

    private function normalizeRiwayatPekerjaanDate($value): ?string
    {
        if (!$value) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (Str::contains(Str::lower($value), ['sekarang', 'sampai sekarang', 'present', 'current'])) {
            return null;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }

        if (preg_match('/^\d{4}-\d{2}$/', $value)) {
            return $value . '-01';
        }

        if (preg_match('/\b(19|20)\d{2}\b/', $value, $matches)) {
            return $matches[0] . '-01-01';
        }

        return null;
    }

    private function normalizeDecimalValue($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $cleanValue = preg_replace('/[^0-9.]/', '', (string) $value);

        if ($cleanValue === '' || !is_numeric($cleanValue)) {
            return null;
        }

        $maxValue = 999999999999999999.99;
        $numericValue = (float) $cleanValue;

        if ($numericValue > $maxValue) {
            $numericValue = $maxValue;
        }

        return number_format($numericValue, 2, '.', '');
    }

    private function dateToYear($value): ?string
    {
        if (!$value) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y');
        }

        $value = trim((string) $value);

        if (preg_match('/\b(19|20)\d{2}\b/', $value, $matches)) {
            return $matches[0];
        }

        return null;
    }

    private function calculateLamaBekerja($startDate, $endDate): ?string
    {
        return $this->calculateLamaBekerjaFromYear(
            $this->dateToYear($startDate),
            $this->dateToYear($endDate)
        );
    }

    private function tablesFor(array $modelClasses, array $fallbackTables): array
    {
        $tables = [];

        foreach ($modelClasses as $modelClass) {
            if (class_exists($modelClass)) {
                try {
                    $tables[] = (new $modelClass())->getTable();
                } catch (\Throwable $error) {
                    //
                }
            }
        }

        return array_values(array_unique(array_filter(array_merge($tables, $fallbackTables))));
    }

    private function putFirstExistingColumn(array &$data, string $table, array $columns, $value): void
    {
        foreach ($columns as $column) {
            if (Schema::hasColumn($table, $column)) {
                $data[$column] = $value;
                return;
            }
        }
    }

    private function findMasterId(array $tables, array $columns, ?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $value = trim($value);

        if (Str::isUuid($value)) {
            foreach ($tables as $table) {
                if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'id')) {
                    continue;
                }

                $query = DB::table($table);

                if (Schema::hasColumn($table, 'deleted_at')) {
                    $query->whereNull('deleted_at');
                }

                $id = $query
                    ->where('id', $value)
                    ->value('id');

                if ($id) {
                    return (string) $id;
                }
            }
        }

        $candidateKey = $this->normalizeForMasterSearch($value);

        foreach ($tables as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'id')) {
                continue;
            }

            foreach ($columns as $column) {
                if (!Schema::hasColumn($table, $column)) {
                    continue;
                }

                $query = DB::table($table)
                    ->select('id', $column)
                    ->whereNotNull($column);

                if (Schema::hasColumn($table, 'deleted_at')) {
                    $query->whereNull('deleted_at');
                }

                $rows = $query->get();

                foreach ($rows as $row) {
                    $label = (string) ($row->{$column} ?? '');

                    if ($this->normalizeForMasterSearch($label) === $candidateKey) {
                        return (string) $row->id;
                    }
                }
            }
        }

        return null;
    }

}
