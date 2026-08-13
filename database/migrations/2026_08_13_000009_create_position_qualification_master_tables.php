<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_kualifikasi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama')->unique();
            $table->string('deskripsi')->nullable();
            $table->boolean('aktif')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('spesifikasi_kualifikasi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('jenis_kualifikasi_id')->constrained('jenis_kualifikasi')->cascadeOnDelete();
            $table->string('nama');
            $table->boolean('aktif')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['jenis_kualifikasi_id', 'nama'], 'spesifikasi_jenis_nama_unique');
        });

        Schema::create('posisi_spesifikasi_kualifikasi', function (Blueprint $table) {
            $table->foreignUuid('posisi_id')->constrained('posisi')->cascadeOnDelete();
            $table->foreignUuid('spesifikasi_kualifikasi_id')->constrained('spesifikasi_kualifikasi')->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['posisi_id', 'spesifikasi_kualifikasi_id'], 'posisi_spesifikasi_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posisi_spesifikasi_kualifikasi');
        Schema::dropIfExists('spesifikasi_kualifikasi');
        Schema::dropIfExists('jenis_kualifikasi');
    }
};
