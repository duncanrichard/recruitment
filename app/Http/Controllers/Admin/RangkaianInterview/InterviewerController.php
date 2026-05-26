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
            'jabatan_id' => ['nullable', 'uuid', 'exists:jabatan,id'],
            'divisi_id' => ['nullable', 'uuid', 'exists:divisi,id'],
        ]);

        $interviewer = Interviewer::create([
            'nama' => $validated['nama'],
            'jabatan_id' => $validated['jabatan_id'] ?? null,
            'divisi_id' => $validated['divisi_id'] ?? null,
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
            'jabatan_id' => ['nullable', 'uuid', 'exists:jabatan,id'],
            'divisi_id' => ['nullable', 'uuid', 'exists:divisi,id'],
        ]);

        $interviewer->update([
            'nama' => $validated['nama'],
            'jabatan_id' => $validated['jabatan_id'] ?? null,
            'divisi_id' => $validated['divisi_id'] ?? null,
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