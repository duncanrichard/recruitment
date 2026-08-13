<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Posisi;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PosisiController extends Controller
{
    public function index()
    {
        return view('pages.admin.index');
    }

    public function list()
    {
        try {
            $tableName = (new Posisi)->getTable();

            if (! Schema::hasTable($tableName)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tabel posisi tidak ditemukan.',
                    'error' => 'Table not found: '.$tableName,
                ], 500);
            }

            $query = Posisi::with('spesifikasi');

            if (Schema::hasColumn($tableName, 'nama_posisi')) {
                $query->orderBy('nama_posisi', 'asc');
            } elseif (Schema::hasColumn($tableName, 'created_at')) {
                $query->orderByDesc('created_at');
            }

            $data = $query->get()->map(fn (Posisi $posisi) => $this->formatPosisi($posisi));

            return response()->json([
                'success' => true,
                'message' => 'Data posisi berhasil diambil.',
                'data' => $data,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data posisi.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_posisi' => [
                'required',
                'string',
                'max:255',
                Rule::unique((new Posisi)->getTable(), 'nama_posisi'),
            ],
            'deskripsi' => ['nullable', 'string'],
            'str_aktif' => ['nullable', Rule::in(['active', 'non_active'])],
            'spesifikasi' => ['required', 'array', 'min:1'],
            'spesifikasi.*' => ['required', 'string', 'max:500'],
        ]);

        try {
            $validated['str_aktif'] = $validated['str_aktif'] ?? 'active';

            $posisi = DB::transaction(function () use ($validated) {
                $posisi = Posisi::create([
                    'nama_posisi' => $validated['nama_posisi'],
                    'deskripsi' => $validated['deskripsi'] ?? null,
                    'str_aktif' => $validated['str_aktif'],
                ]);
                $this->syncSpesifikasi($posisi, $validated['spesifikasi']);
                return $posisi;
            });

            return response()->json([
                'success' => true,
                'message' => 'Data posisi berhasil disimpan.',
                'data' => $this->formatPosisi($posisi->load('spesifikasi')),
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data posisi gagal disimpan.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $posisi = Posisi::findOrFail($id);

            $validated = $request->validate([
                'nama_posisi' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique((new Posisi)->getTable(), 'nama_posisi')
                        ->ignore($posisi->id, 'id'),
                ],
                'deskripsi' => ['nullable', 'string'],
                'str_aktif' => ['nullable', Rule::in(['active', 'non_active'])],
                'spesifikasi' => ['required', 'array', 'min:1'],
                'spesifikasi.*' => ['required', 'string', 'max:500'],
            ]);

            $validated['str_aktif'] = $validated['str_aktif'] ?? 'active';

            DB::transaction(function () use ($posisi, $validated) {
                $posisi->update([
                    'nama_posisi' => $validated['nama_posisi'],
                    'deskripsi' => $validated['deskripsi'] ?? null,
                    'str_aktif' => $validated['str_aktif'],
                ]);
                $this->syncSpesifikasi($posisi, $validated['spesifikasi']);
            });

            return response()->json([
                'success' => true,
                'message' => 'Data posisi berhasil diperbarui.',
                'data' => $this->formatPosisi($posisi->fresh()->load('spesifikasi')),
            ]);
        } catch (ModelNotFoundException $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data posisi tidak ditemukan.',
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data posisi gagal diperbarui.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $posisi = Posisi::findOrFail($id);

            $posisi->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data posisi berhasil dihapus.',
            ]);
        } catch (ModelNotFoundException $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data posisi tidak ditemukan.',
            ], 404);
        } catch (QueryException $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data posisi tidak bisa dihapus karena masih digunakan oleh data lain.',
                'error' => $th->getMessage(),
            ], 409);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data posisi gagal dihapus.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    private function formatPosisi(Posisi $posisi): Posisi
    {
        $posisi->setAttribute('spesifikasi_items', $posisi->spesifikasi->map(fn ($item) => [
            'id' => $item->id,
            'spesifikasi' => $item->spesifikasi,
        ])->values());

        return $posisi;
    }

    private function syncSpesifikasi(Posisi $posisi, array $items): void
    {
        $posisi->spesifikasi()->delete();
        foreach (array_values($items) as $index => $item) {
            $posisi->spesifikasi()->create([
                'spesifikasi' => trim($item),
                'urutan' => $index,
            ]);
        }
    }
}
