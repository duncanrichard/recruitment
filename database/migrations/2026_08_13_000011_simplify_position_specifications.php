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
        Schema::create('posisi_spesifikasi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('posisi_id')->constrained('posisi')->cascadeOnDelete();
            $table->string('spesifikasi', 500);
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();
            $table->index(['posisi_id', 'urutan'], 'posisi_spesifikasi_order_idx');
        });

        if (Schema::hasTable('posisi_kualifikasi')) {
            DB::table('posisi_kualifikasi')->orderBy('posisi_id')->orderBy('urutan')->get()
                ->each(function ($item) {
                    DB::table('posisi_spesifikasi')->insert([
                        'id' => (string) Str::uuid(),
                        'posisi_id' => $item->posisi_id,
                        'spesifikasi' => $item->nilai,
                        'urutan' => $item->urutan ?? 0,
                        'created_at' => $item->created_at ?? now(),
                        'updated_at' => $item->updated_at ?? now(),
                    ]);
                });
        }

        Schema::dropIfExists('posisi_kualifikasi');
        Schema::dropIfExists('posisi_spesifikasi_kualifikasi');
        Schema::dropIfExists('spesifikasi_kualifikasi');
        Schema::dropIfExists('jenis_kualifikasi');
    }

    public function down(): void
    {
        Schema::dropIfExists('posisi_spesifikasi');
    }
};
