<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PermintaanKandidatRecruitment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PermintaanKandidatRecruitmentController extends Controller
{
    private array $tipePekerjaanOptions = [
        'Kontrak',
        'Tetap',
        'Paruh Waktu',
        'Magang',
        'Freelance',
        'Lainnya',
    ];

    private array $jenisKelaminOptions = [
        'Laki-laki',
        'Perempuan',
        'Laki-laki / Perempuan',
    ];

    private array $urgentOptions = [
        'Rendah',
        'Sedang',
        'Tinggi',
        'Sangat Urgent',
    ];

    private array $alasanOptions = [
        'Penggantian',
        'Baru Divisi',
        'Penambahan Karyawan',
        'Lainnya',
    ];

    private array $statusOptions = [
        'Draft',
        'Diajukan',
        'Diproses',
        'Selesai',
        'Dibatalkan',
    ];

    public function index()
    {
        return view('pages.admin.index');
    }

    public function list(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'status_permintaan' => ['nullable', Rule::in($this->statusOptions)],
            'urgent_permintaan' => ['nullable', Rule::in($this->urgentOptions)],
        ]);

        $query = PermintaanKandidatRecruitment::query();

        if (!empty($validated['tanggal_mulai'])) {
            $query->whereDate('tanggal_permintaan', '>=', $validated['tanggal_mulai']);
        }

        if (!empty($validated['tanggal_selesai'])) {
            $query->whereDate('tanggal_permintaan', '<=', $validated['tanggal_selesai']);
        }

        if (!empty($validated['status_permintaan'])) {
            $query->where('status_permintaan', $validated['status_permintaan']);
        }

        if (!empty($validated['urgent_permintaan'])) {
            $query->where('urgent_permintaan', $validated['urgent_permintaan']);
        }

        if (!empty($validated['search'])) {
            $keyword = trim($validated['search']);

            $query->where(function ($q) use ($keyword) {
                $q->where('pt_membutuhkan', 'like', "%{$keyword}%")
                    ->orWhere('divisi_departemen', 'like', "%{$keyword}%")
                    ->orWhere('permintaan_oleh', 'like', "%{$keyword}%")
                    ->orWhere('nama_posisi_jabatan', 'like', "%{$keyword}%")
                    ->orWhere('lokasi_kerja', 'like', "%{$keyword}%")
                    ->orWhere('tipe_pekerjaan', 'like', "%{$keyword}%")
                    ->orWhere('urgent_permintaan', 'like', "%{$keyword}%")
                    ->orWhere('alasan_permintaan', 'like', "%{$keyword}%")
                    ->orWhere('status_permintaan', 'like', "%{$keyword}%");
            });
        }

        $data = $query
            ->latest()
            ->get()
            ->map(function (PermintaanKandidatRecruitment $item) {
                return $this->mapData($item);
            });

        return response()->json([
            'success' => true,
            'message' => 'Data permintaan kandidat recruitment berhasil diambil.',
            'data' => $data,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validatePayload($request);

        $data = PermintaanKandidatRecruitment::query()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Permintaan kandidat recruitment berhasil dibuat.',
            'data' => $this->mapData($data),
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $data = PermintaanKandidatRecruitment::query()->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail permintaan kandidat recruitment berhasil diambil.',
            'data' => $this->mapData($data),
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $data = PermintaanKandidatRecruitment::query()->findOrFail($id);

        $validated = $this->validatePayload($request);

        $data->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Permintaan kandidat recruitment berhasil diperbarui.',
            'data' => $this->mapData($data->fresh()),
        ]);
    }

    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'status_permintaan' => ['required', Rule::in($this->statusOptions)],
        ]);

        $data = PermintaanKandidatRecruitment::query()->findOrFail($id);

        $data->update([
            'status_permintaan' => $validated['status_permintaan'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status permintaan kandidat recruitment berhasil diperbarui.',
            'data' => $this->mapData($data->fresh()),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $data = PermintaanKandidatRecruitment::query()->findOrFail($id);

        $data->delete();

        return response()->json([
            'success' => true,
            'message' => 'Permintaan kandidat recruitment berhasil dihapus.',
        ]);
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'pt_membutuhkan' => ['nullable', 'string', 'max:255'],
            'divisi_departemen' => ['nullable', 'string', 'max:255'],
            'permintaan_oleh' => ['nullable', 'string', 'max:255'],
            'tanggal_permintaan' => ['nullable', 'date'],

            'deskripsi_permintaan' => ['nullable', 'string'],

            'nama_posisi_jabatan' => ['required', 'string', 'max:255'],
            'jumlah_karyawan' => ['required', 'integer', 'min:1'],
            'lokasi_kerja' => ['nullable', 'string', 'max:255'],

            'tipe_pekerjaan' => ['nullable', Rule::in($this->tipePekerjaanOptions)],
            'jadwal_kerja' => ['nullable', 'string', 'max:255'],
            'deskripsi_pekerjaan' => ['nullable', 'string'],
            'gaji_benefit' => ['nullable', 'string'],

            'pendidikan_minimum' => ['nullable', 'string', 'max:255'],
            'usia' => ['nullable', 'string', 'max:255'],
            'jenis_kelamin' => ['nullable', Rule::in($this->jenisKelaminOptions)],
            'pengalaman_kerja' => ['nullable', 'string', 'max:255'],
            'keterampilan_teknis' => ['nullable', 'string'],
            'keterampilan_interpersonal' => ['nullable', 'string'],
            'syarat_khusus' => ['nullable', 'string'],
            'keahlian_khusus' => ['nullable', 'string'],
            'sertifikat' => ['nullable', 'string'],

            'tanggal_mulai_diperlukan' => ['nullable', 'date'],
            'urgent_permintaan' => ['nullable', Rule::in($this->urgentOptions)],
            'alasan_permintaan' => ['nullable', Rule::in($this->alasanOptions)],

            'karakter_pribadi' => ['nullable', 'string'],
            'hasil_test_tertulis' => ['nullable', 'string'],
            'permintaan_khusus' => ['nullable', 'string'],
            'karakter_profesional' => ['nullable', 'string'],

            'proses_seleksi' => ['nullable', 'string'],
            'materi_ppt' => ['nullable', 'string'],
            'informasi_tambahan' => ['nullable', 'string'],
            'penyebaran_iklan' => ['nullable', 'string'],

            'status_permintaan' => ['nullable', Rule::in($this->statusOptions)],
        ]);
    }

    private function mapData(PermintaanKandidatRecruitment $item): array
    {
        return [
            'id' => $item->id,

            'pt_membutuhkan' => $item->pt_membutuhkan,
            'divisi_departemen' => $item->divisi_departemen,
            'permintaan_oleh' => $item->permintaan_oleh,
            'tanggal_permintaan' => optional($item->tanggal_permintaan)->format('Y-m-d'),
            'tanggal_permintaan_format' => optional($item->tanggal_permintaan)->translatedFormat('d F Y'),

            'deskripsi_permintaan' => $item->deskripsi_permintaan,

            'nama_posisi_jabatan' => $item->nama_posisi_jabatan,
            'jumlah_karyawan' => $item->jumlah_karyawan,
            'lokasi_kerja' => $item->lokasi_kerja,

            'tipe_pekerjaan' => $item->tipe_pekerjaan,
            'jadwal_kerja' => $item->jadwal_kerja,
            'deskripsi_pekerjaan' => $item->deskripsi_pekerjaan,
            'gaji_benefit' => $item->gaji_benefit,

            'pendidikan_minimum' => $item->pendidikan_minimum,
            'usia' => $item->usia,
            'jenis_kelamin' => $item->jenis_kelamin,
            'pengalaman_kerja' => $item->pengalaman_kerja,
            'keterampilan_teknis' => $item->keterampilan_teknis,
            'keterampilan_interpersonal' => $item->keterampilan_interpersonal,
            'syarat_khusus' => $item->syarat_khusus,
            'keahlian_khusus' => $item->keahlian_khusus,
            'sertifikat' => $item->sertifikat,

            'tanggal_mulai_diperlukan' => optional($item->tanggal_mulai_diperlukan)->format('Y-m-d'),
            'tanggal_mulai_diperlukan_format' => optional($item->tanggal_mulai_diperlukan)->translatedFormat('d F Y'),

            'urgent_permintaan' => $item->urgent_permintaan,
            'alasan_permintaan' => $item->alasan_permintaan,

            'karakter_pribadi' => $item->karakter_pribadi,
            'hasil_test_tertulis' => $item->hasil_test_tertulis,
            'permintaan_khusus' => $item->permintaan_khusus,
            'karakter_profesional' => $item->karakter_profesional,

            'proses_seleksi' => $item->proses_seleksi,
            'materi_ppt' => $item->materi_ppt,
            'informasi_tambahan' => $item->informasi_tambahan,
            'penyebaran_iklan' => $item->penyebaran_iklan,

            'status_permintaan' => $item->status_permintaan,

            'created_at' => optional($item->created_at)->format('Y-m-d H:i:s'),
            'updated_at' => optional($item->updated_at)->format('Y-m-d H:i:s'),
        ];
    }
}