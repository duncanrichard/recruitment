<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Tabel opsi_kacamata
        |--------------------------------------------------------------------------
        */
        Schema::create('opsi_kacamata', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('opsi')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        /*
        |--------------------------------------------------------------------------
        | Tabel data_riwayat_kesehatan
        |--------------------------------------------------------------------------
        */
        Schema::create('data_riwayat_kesehatan', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('data_riwayat_diri_id')
                ->constrained('data_riwayat_diri')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->enum('buta_warna', [
                'Ya',
                'Partial',
                'Tidak',
            ])->nullable();

            $table->foreignUuid('opsi_kacamata_id')
                ->nullable()
                ->constrained('opsi_kacamata')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->enum('alat_bantu_dengar', [
                'Ya',
                'Tidak',
            ])->nullable();

            $table->enum('menulis_dengan_tangan', [
                'kanan',
                'kiri',
            ])->nullable();

            $table->enum('sering_gemetar', [
                'Ya',
                'Tidak',
            ])->nullable();

            $table->enum('tangan_sering_berkeringat', [
                'Ya',
                'Tidak',
            ])->nullable();

            $table->enum('penyakit_menular', [
                'Ya',
                'Tidak',
            ])->nullable();

            $table->enum('program_kehamilan', [
                'Ya',
                'Tidak',
            ])->nullable();

            $table->enum('punya_alergi', [
                'Ya',
                'Tidak',
            ])->nullable();

            $table->text('nama_alergi')->nullable();

            $table->enum('punya_penyakit_genetik', [
                'Ya',
                'Tidak',
            ])->nullable();

            $table->text('nama_penyakit')->nullable();

            $table->enum('riwayat_kronis', [
                'Ya',
                'Tidak',
            ])->nullable();

            $table->enum('pengobatan_psikolog', [
                'Ya',
                'Tidak',
            ])->nullable();

            $table->text('kapan_dilakukan')->nullable();

            $table->enum('pernah_kecelakaan', [
                'Ya',
                'Tidak',
            ])->nullable();

            $table->text('bagian_tubuh_kecelakaan')->nullable();

            $table->enum('pernah_operasi', [
                'Ya',
                'Tidak',
            ])->nullable();

            $table->text('diagnosa_dokter')->nullable();

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
        Schema::dropIfExists('data_riwayat_kesehatan');
        Schema::dropIfExists('opsi_kacamata');
    }
};