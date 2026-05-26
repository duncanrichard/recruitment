<?php

namespace App\Http\Controllers\Admin\RangkaianInterview;

use App\Http\Controllers\Controller;
use App\Models\Interviewer;
use App\Models\JadwalInterview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JadwalInterviewController extends Controller
{
    public function list()
    {
        $data = JadwalInterview::query()
            ->with([
                'panelis' => function ($query) {
                    $query->select('interviewers.id', 'interviewers.nama');
                },
            ])
            ->select([
                'id',
                'judul_interview',
                'jadwal_interview',
                'created_at',
                'updated_at',
            ])
            ->orderBy('jadwal_interview', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data jadwal interview berhasil diambil.',
            'data' => $data,
        ]);
    }

    public function interviewers()
    {
        $data = Interviewer::query()
            ->select('id', 'nama')
            ->orderBy('nama', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data interviewer berhasil diambil.',
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul_interview' => ['required', 'string', 'max:255'],
            'jadwal_interview' => ['required', 'date', 'after_or_equal:now'],
            'interviewer_ids' => ['required', 'array', 'min:1'],
            'interviewer_ids.*' => ['required', 'uuid', 'exists:interviewers,id'],
        ]);

        $jadwalInterview = DB::transaction(function () use ($validated) {
            $jadwalInterview = JadwalInterview::create([
                'judul_interview' => $validated['judul_interview'],
                'jadwal_interview' => $validated['jadwal_interview'],
            ]);

            $rows = collect($validated['interviewer_ids'])
                ->unique()
                ->map(function ($interviewerId) use ($jadwalInterview) {
                    return [
                        'id' => (string) Str::uuid(),
                        'jadwal_interview_id' => $jadwalInterview->id,
                        'interviewer_id' => $interviewerId,
                        'created_at' => now(),
                    ];
                })
                ->values()
                ->all();

            DB::table('jadwal_interview_panelis')->insert($rows);

            return $jadwalInterview->load('panelis:id,nama');
        });

        return response()->json([
            'success' => true,
            'message' => 'Data jadwal interview berhasil disimpan.',
            'data' => $jadwalInterview,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $jadwalInterview = JadwalInterview::findOrFail($id);

        $validated = $request->validate([
            'judul_interview' => ['required', 'string', 'max:255'],
            'jadwal_interview' => ['required', 'date', 'after_or_equal:now'],
            'interviewer_ids' => ['required', 'array', 'min:1'],
            'interviewer_ids.*' => ['required', 'uuid', 'exists:interviewers,id'],
        ]);

        $jadwalInterview = DB::transaction(function () use ($jadwalInterview, $validated) {
            $jadwalInterview->update([
                'judul_interview' => $validated['judul_interview'],
                'jadwal_interview' => $validated['jadwal_interview'],
            ]);

            DB::table('jadwal_interview_panelis')
                ->where('jadwal_interview_id', $jadwalInterview->id)
                ->delete();

            $rows = collect($validated['interviewer_ids'])
                ->unique()
                ->map(function ($interviewerId) use ($jadwalInterview) {
                    return [
                        'id' => (string) Str::uuid(),
                        'jadwal_interview_id' => $jadwalInterview->id,
                        'interviewer_id' => $interviewerId,
                        'created_at' => now(),
                    ];
                })
                ->values()
                ->all();

            DB::table('jadwal_interview_panelis')->insert($rows);

            return $jadwalInterview->load('panelis:id,nama');
        });

        return response()->json([
            'success' => true,
            'message' => 'Data jadwal interview berhasil diperbarui.',
            'data' => $jadwalInterview,
        ]);
    }

    public function destroy(string $id)
    {
        $jadwalInterview = JadwalInterview::findOrFail($id);

        DB::transaction(function () use ($jadwalInterview) {
            DB::table('jadwal_interview_panelis')
                ->where('jadwal_interview_id', $jadwalInterview->id)
                ->delete();

            $jadwalInterview->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Data jadwal interview berhasil dihapus.',
        ]);
    }
}