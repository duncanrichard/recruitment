<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_riwayat_pekerjaan', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('data_riwayat_diri_id')
                ->constrained('data_riwayat_diri')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('nama_perusahaan')->nullable();

            $table->text('posisi_pekerjaan_terakhir')->nullable();

            $table->date('periode_kerja_awal')->nullable();

            $table->date('periode_kerja_akhir')->nullable();

            $table->decimal('gaji_terakhir', 15, 2)->nullable();

            $table->enum('referensi_kerja', [
                'Ya',
                'Tidak',
            ])->nullable();

            $table->text('nama_refrensi')->nullable();

            $table->text('telp_refrensi')->nullable();

            $table->enum('refrensi_rekan_kerja', [
                'Ya',
                'Tidak',
            ])->nullable();

            $table->text('nama_refrensi_rekan')->nullable();

            $table->text('telp_refrensi_rekan')->nullable();

            $table->enum('refrensi_kerabat', [
                'Ya',
                'Tidak',
            ])->nullable();

            $table->text('nama_refrensi_kerabat')->nullable();

            $table->text('telp_refrensi_kerabat')->nullable();

            $table->timestamps();

            $table->softDeletes();

            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->foreign('deleted_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_riwayat_pekerjaan');
    }
};