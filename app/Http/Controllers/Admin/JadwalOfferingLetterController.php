<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HasilReviewManagement;
use App\Models\JadwalOfferingLetter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JadwalOfferingLetterController extends Controller
{
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

        return response()->json([
            'success' => true,
            'message' => 'Jadwal Offering Letter berhasil dibuat.',
            'data' => $data,
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

        return response()->json([
            'success' => true,
            'message' => 'Jadwal Offering Letter berhasil diperbarui.',
            'data' => $data,
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

    private function getNamaKandidat(?HasilReviewManagement $item): string
    {
        if (!$item) {
            return '-';
        }

        return $item->hasilInterview?->kandidat?->nama_lengkap ?: '-';
    }

    private function makeKandidatLabel(?HasilReviewManagement $item): string
    {
        return $this->getNamaKandidat($item);
    }
}
