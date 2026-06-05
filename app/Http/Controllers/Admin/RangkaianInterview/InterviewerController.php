<?php

namespace App\Http\Controllers\Admin\RangkaianInterview;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use App\Models\Interviewer;
use App\Models\Jabatan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InterviewerController extends Controller
{
    public function list()
    {
        $data = Interviewer::query()
            ->with([
                'jabatan:id,nama',
                'divisi:id,nama',
            ])
            ->select([
                'id',
                'nama',
                'no_wa',
                'jabatan_id',
                'divisi_id',
                'created_at',
                'updated_at',
            ])
            ->orderBy('nama', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data interviewer berhasil diambil.',
            'data' => $data,
        ]);
    }

    public function options()
    {
        return response()->json([
            'success' => true,
            'message' => 'Data option berhasil diambil.',
            'data' => [
                'jabatan' => Jabatan::query()
                    ->select('id', 'nama')
                    ->orderBy('nama', 'asc')
                    ->get(),

                'divisi' => Divisi::query()
                    ->select('id', 'nama')
                    ->orderBy('nama', 'asc')
                    ->get(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:255',
                Rule::unique('interviewers', 'nama')->whereNull('deleted_at'),
            ],
            'no_wa' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[0-9+\-\s()]+$/',
            ],
            'jabatan_id' => ['nullable', 'uuid', 'exists:jabatan,id'],
            'divisi_id' => ['nullable', 'uuid', 'exists:divisi,id'],
        ], [
            'nama.required' => 'Nama interviewer wajib diisi.',
            'nama.unique' => 'Nama interviewer sudah digunakan.',
            'no_wa.regex' => 'Format No WA tidak valid.',
            'no_wa.max' => 'No WA maksimal 50 karakter.',
            'jabatan_id.uuid' => 'Data jabatan tidak valid.',
            'jabatan_id.exists' => 'Data jabatan tidak ditemukan.',
            'divisi_id.uuid' => 'Data divisi tidak valid.',
            'divisi_id.exists' => 'Data divisi tidak ditemukan.',
        ]);

        $interviewer = Interviewer::create([
            'nama' => $validated['nama'],
            'no_wa' => $validated['no_wa'] ?? null,
            'jabatan_id' => $validated['jabatan_id'] ?? null,
            'divisi_id' => $validated['divisi_id'] ?? null,
        ]);

        $interviewer->load([
            'jabatan:id,nama',
            'divisi:id,nama',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data interviewer berhasil disimpan.',
            'data' => $interviewer,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $interviewer = Interviewer::findOrFail($id);

        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:255',
                Rule::unique('interviewers', 'nama')
                    ->ignore($interviewer->id, 'id')
                    ->whereNull('deleted_at'),
            ],
            'no_wa' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[0-9+\-\s()]+$/',
            ],
            'jabatan_id' => ['nullable', 'uuid', 'exists:jabatan,id'],
            'divisi_id' => ['nullable', 'uuid', 'exists:divisi,id'],
        ], [
            'nama.required' => 'Nama interviewer wajib diisi.',
            'nama.unique' => 'Nama interviewer sudah digunakan.',
            'no_wa.regex' => 'Format No WA tidak valid.',
            'no_wa.max' => 'No WA maksimal 50 karakter.',
            'jabatan_id.uuid' => 'Data jabatan tidak valid.',
            'jabatan_id.exists' => 'Data jabatan tidak ditemukan.',
            'divisi_id.uuid' => 'Data divisi tidak valid.',
            'divisi_id.exists' => 'Data divisi tidak ditemukan.',
        ]);

        $interviewer->update([
            'nama' => $validated['nama'],
            'no_wa' => $validated['no_wa'] ?? null,
            'jabatan_id' => $validated['jabatan_id'] ?? null,
            'divisi_id' => $validated['divisi_id'] ?? null,
        ]);

        $interviewer->load([
            'jabatan:id,nama',
            'divisi:id,nama',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data interviewer berhasil diperbarui.',
            'data' => $interviewer,
        ]);
    }

    public function destroy(string $id)
    {
        $interviewer = Interviewer::findOrFail($id);
        $interviewer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data interviewer berhasil dihapus.',
        ]);
    }
}
