<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posisi_kualifikasi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('posisi_id')->constrained('posisi')->cascadeOnDelete();
            $table->foreignUuid('jenis_kualifikasi_id')->constrained('jenis_kualifikasi')->cascadeOnDelete();
            $table->string('nilai', 500);
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();
            $table->index(['posisi_id', 'jenis_kualifikasi_id'], 'posisi_kualifikasi_lookup_idx');
        });

        if (Schema::hasTable('posisi_spesifikasi_kualifikasi')) {
            DB::table('posisi_spesifikasi_kualifikasi as pivot')
                ->join('spesifikasi_kualifikasi as spesifikasi', 'spesifikasi.id', '=', 'pivot.spesifikasi_kualifikasi_id')
                ->select('pivot.posisi_id', 'spesifikasi.jenis_kualifikasi_id', 'spesifikasi.nama', 'pivot.created_at', 'pivot.updated_at')
                ->orderBy('pivot.posisi_id')
                ->get()
                ->each(function ($item, $index) {
                    DB::table('posisi_kualifikasi')->insert([
                        'id' => (string) Str::uuid(),
                        'posisi_id' => $item->posisi_id,
                        'jenis_kualifikasi_id' => $item->jenis_kualifikasi_id,
                        'nilai' => $item->nama,
                        'urutan' => $index,
                        'created_at' => $item->created_at ?? now(),
                        'updated_at' => $item->updated_at ?? now(),
                    ]);
                });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('posisi_kualifikasi');
    }
};
