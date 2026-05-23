<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agama;
use App\Models\DataPerusahaan;
use App\Models\DataRiwayatDiri;
use App\Models\Kewarganegaraan;
use App\Models\Pendidikan;
use App\Models\Posisi;
use App\Models\SumberInformasi;
use App\Models\StatusPernikahan;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DataPelamarController extends Controller
{
    private array $relations = [
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

    public function index()
    {
        return view('pages.admin.data-pelamar.index', [
            'title' => 'Data Pelamar',
        ]);
    }

    public function list(): JsonResponse
    {
        $data = DataRiwayatDiri::query()
            ->with($this->safeRelations())
            ->latest()
            ->get()
            ->map(function ($item) {
                $item->pendaftaran_url = $this->makePendaftaranUrl($item->token);

                return $item;
            });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function posisiList(): JsonResponse
    {
        $data = Posisi::query()
            ->orderBy('nama_posisi')
            ->get([
                'id',
                'nama_posisi',
                'str_aktif',
            ]);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function perusahaanList(): JsonResponse
    {
        $data = DataPerusahaan::query()
            ->orderBy('nama_perusahaan')
            ->get([
                'id',
                'kode',
                'nama_perusahaan',
            ]);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function sumberInformasiList(): JsonResponse
    {
        $data = SumberInformasi::query()
            ->orderBy('informasi')
            ->get([
                'id',
                'informasi',
            ]);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function pendidikanList(): JsonResponse
    {
        $data = Pendidikan::query()
            ->orderBy('pendidikan')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function agamaList(): JsonResponse
    {
        $data = Agama::query()
            ->orderBy('agama')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function kewarganegaraanList(): JsonResponse
    {
        $data = Kewarganegaraan::query()
            ->orderBy('kewarganegaraan')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function statusPernikahanList(): JsonResponse
    {
        $data = StatusPernikahan::query()
            ->orderBy('status_pernikahan')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validatePelamar($request);

        $data = DataRiwayatDiri::query()->create($validated);

        $freshData = $data->fresh($this->safeRelations());
        $freshData->pendaftaran_url = $this->makePendaftaranUrl($freshData->token);

        return response()->json([
            'success' => true,
            'message' => 'Data pelamar berhasil disimpan.',
            'data' => $freshData,
            'token' => $freshData->token,
            'pendaftaran_url' => $freshData->pendaftaran_url,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $data = DataRiwayatDiri::query()
            ->with($this->safeRelations())
            ->findOrFail($id);

        $data = $this->appendExtraData($data);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function detail(string $id)
    {
        return view('pages.admin.data-pelamar.detail', [
            'title' => 'Detail Data Pelamar',
            'id' => $id,
        ]);
    }

    public function detailData(string $id): JsonResponse
    {
        $data = DataRiwayatDiri::query()
            ->with($this->safeRelations())
            ->findOrFail($id);

        $data = $this->appendExtraData($data);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function showByToken(string $token)
    {
        $data = DataRiwayatDiri::query()
            ->with($this->safeRelations())
            ->where('token', $token)
            ->firstOrFail();

        $data = $this->appendExtraData($data);

        return view('pages.pendaftaran.index', [
            'title' => 'Pendaftaran',
            'token' => $token,
            'pelamar' => $data,
        ]);
    }

    public function findByToken(string $token): JsonResponse
    {
        $pelamar = DataRiwayatDiri::query()
            ->with($this->safeRelations())
            ->where('token', $token)
            ->first();

        if (!$pelamar) {
            return response()->json([
                'success' => false,
                'message' => 'Token pelamar tidak ditemukan.',
                'data' => null,
            ], 404);
        }

        $pelamar = $this->appendExtraData($pelamar);

        return response()->json([
            'success' => true,
            'message' => 'Data pelamar ditemukan.',
            'data' => $pelamar,
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $data = DataRiwayatDiri::query()->findOrFail($id);

        $validated = $this->validatePelamar($request, true);

        if (!$request->has('str_aktif')) {
            unset($validated['str_aktif']);
        }

        $data->update($validated);

        $freshData = $data->fresh($this->safeRelations());
        $freshData = $this->appendExtraData($freshData);

        return response()->json([
            'success' => true,
            'message' => 'Data pelamar berhasil diperbarui.',
            'data' => $freshData,
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $data = DataRiwayatDiri::query()->findOrFail($id);

        try {
            $data->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data pelamar berhasil dihapus.',
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data pelamar tidak bisa dihapus karena masih digunakan oleh data lain.',
            ], 409);
        }
    }

    private function validatePelamar(Request $request, bool $isUpdate = false): array
    {
        return $request->validate([
            'posisi_yang_dilamar' => [
                'required',
                'uuid',
                Rule::exists('posisi', 'id'),
            ],
            'perusahaan_dilamar' => [
                'required',
                'uuid',
                Rule::exists('data_perusahaan', 'id'),
            ],
            'sumber_informasi_id' => [
                'required',
                'uuid',
                Rule::exists('sumber_informasi', 'id'),
            ],
            'nama_lengkap' => [
                'required',
                'string',
                'max:255',
            ],
            'nama_panggil' => [
                'nullable',
                'string',
                'max:255',
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'pendidikan_id' => [
                'nullable',
                'uuid',
                Rule::exists('pendidikan', 'id'),
            ],
            'jurusan' => [
                'nullable',
                'string',
                'max:255',
            ],
            'nama_institusi' => [
                'nullable',
                'string',
                'max:255',
            ],
            'agama_id' => [
                'nullable',
                'uuid',
                Rule::exists('agama', 'id'),
            ],
            'tanggal_lahir' => [
                'nullable',
                'date',
            ],
            'tanggal_skrining' => [
                'nullable',
                'date',
            ],
            'alamat_ktp' => [
                'nullable',
                'string',
            ],
            'alamat_domisili' => [
                'nullable',
                'string',
            ],
            'kewarganegaraan_id' => [
                'nullable',
                'uuid',
                Rule::exists('kewarganegaraan', 'id'),
            ],
            'status_pernikahan_id' => [
                'nullable',
                'uuid',
                Rule::exists('status_pernikahan', 'id'),
            ],
            'no_wa' => [
                'nullable',
                'string',
                'max:50',
            ],
            'gol_darah' => [
                'nullable',
                'string',
                'max:10',
            ],
            'tinggi_badan' => [
                'nullable',
                'numeric',
            ],
            'berat_badan' => [
                'nullable',
                'numeric',
            ],
            'str_aktif' => [
                'nullable',
                Rule::in(['active', 'non_active']),
            ],
        ], [
            'posisi_yang_dilamar.required' => 'Posisi yang dilamar wajib diisi.',
            'posisi_yang_dilamar.uuid' => 'Posisi yang dilamar tidak valid.',
            'posisi_yang_dilamar.exists' => 'Posisi yang dilamar tidak ditemukan.',
            'perusahaan_dilamar.required' => 'Perusahaan dilamar wajib diisi.',
            'perusahaan_dilamar.uuid' => 'Perusahaan dilamar tidak valid.',
            'perusahaan_dilamar.exists' => 'Perusahaan dilamar tidak ditemukan.',
            'sumber_informasi_id.required' => 'Sumber informasi wajib diisi.',
            'sumber_informasi_id.uuid' => 'Sumber informasi tidak valid.',
            'sumber_informasi_id.exists' => 'Sumber informasi tidak ditemukan.',
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'email.email' => 'Format email tidak valid.',
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
        $data->pendaftaran_url = $this->makePendaftaranUrl($data->token);

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

        return $data;
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

    private function safeRelations(): array
    {
        /*
         * Jika salah satu relasi belum dibuat di model DataRiwayatDiri,
         * hapus nama relasinya dari array $relations di atas.
         * Method ini tetap mengembalikan array relasi utama agar controller rapi.
         */
        return $this->relations;
    }
}
