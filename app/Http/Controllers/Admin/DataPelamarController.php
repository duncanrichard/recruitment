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
    ];

    public function index()
    {
        return view('pages.admin.data-pelamar.index', [
            'title' => 'Data Pelamar',
        ]);
    }

    public function list()
    {
        $data = DataRiwayatDiri::query()
            ->with($this->relations)
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

    public function posisiList()
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

    public function perusahaanList()
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

    public function sumberInformasiList()
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

    public function pendidikanList()
    {
        $data = Pendidikan::query()
            ->orderBy('pendidikan')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function agamaList()
    {
        $data = Agama::query()
            ->orderBy('agama')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function kewarganegaraanList()
    {
        $data = Kewarganegaraan::query()
            ->orderBy('kewarganegaraan')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function statusPernikahanList()
    {
        $data = StatusPernikahan::query()
            ->orderBy('status_pernikahan')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePelamar($request);

        $data = DataRiwayatDiri::create($validated);

        $freshData = $data->fresh($this->relations);
        $freshData->pendaftaran_url = $this->makePendaftaranUrl($freshData->token);

        return response()->json([
            'success' => true,
            'message' => 'Data pelamar berhasil disimpan.',
            'data' => $freshData,
            'token' => $freshData->token,
            'pendaftaran_url' => $freshData->pendaftaran_url,
        ], 201);
    }

    public function show($id)
    {
        $data = DataRiwayatDiri::query()
            ->with($this->relations)
            ->findOrFail($id);

        $data->pendaftaran_url = $this->makePendaftaranUrl($data->token);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function showByToken(string $token)
    {
        $data = DataRiwayatDiri::query()
            ->with($this->relations)
            ->where('token', $token)
            ->firstOrFail();

        $data->pendaftaran_url = $this->makePendaftaranUrl($data->token);

        return view('pages.pendaftaran.index', [
            'title' => 'Pendaftaran',
            'token' => $token,
            'pelamar' => $data,
        ]);
    }

    public function update(Request $request, $id)
    {
        $data = DataRiwayatDiri::query()->findOrFail($id);

        $validated = $this->validatePelamar($request, true);

        if (!$request->has('str_aktif')) {
            unset($validated['str_aktif']);
        }

        $data->update($validated);

        $freshData = $data->fresh($this->relations);
        $freshData->pendaftaran_url = $this->makePendaftaranUrl($freshData->token);

        return response()->json([
            'success' => true,
            'message' => 'Data pelamar berhasil diperbarui.',
            'data' => $freshData,
        ]);
    }

    public function destroy($id)
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
    public function pendaftaranIndex()
{
    return view('pages.pendaftaran.index', [
        'title' => 'Pendaftaran',
        'token' => null,
        'pelamar' => null,
    ]);
}

public function pendaftaranShow(string $token)
{
    $pelamar = DataRiwayatDiri::query()
        ->with([
            'pendidikan',
            'agama',
            'kewarganegaraan',
            'statusPernikahan',
            'posisi',
            'perusahaan',
            'sosialMedia',
            'sumberInformasi',
        ])
        ->where('token', $token)
        ->firstOrFail();

    return view('pages.pendaftaran.index', [
        'title' => 'Pendaftaran',
        'token' => $token,
        'pelamar' => $pelamar,
    ]);
}

public function findByToken(string $token)
{
    $pelamar = DataRiwayatDiri::query()
        ->with([
            'pendidikan',
            'agama',
            'kewarganegaraan',
            'statusPernikahan',
            'posisi',
            'perusahaan',
            'sosialMedia',
            'sumberInformasi',
        ])
        ->where('token', $token)
        ->first();

    if (!$pelamar) {
        return response()->json([
            'success' => false,
            'message' => 'Token pelamar tidak ditemukan.',
            'data' => null,
        ], 404);
    }

    return response()->json([
        'success' => true,
        'message' => 'Data pelamar ditemukan.',
        'data' => $pelamar,
    ]);
}
}