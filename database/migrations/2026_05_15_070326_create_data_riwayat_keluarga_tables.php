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
        | Tabel data_riwayat_keluarga
        |--------------------------------------------------------------------------
        */
        Schema::create('data_riwayat_keluarga', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('data_riwayat_diri_id')
                ->constrained('data_riwayat_diri')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('nama_ayah_kandung')->nullable();

            $table->string('pekerjaan_ayah_kandung')->nullable();

            $table->string('nama_ibu_kandung')->nullable();

            $table->string('pekerjaan_ibu_kandung')->nullable();

            $table->string('nama_suami_istri')->nullable();

            $table->string('pekerjaan_suami_istri')->nullable();

            $table->string('tlpn_suami_istri')->nullable();

            $table->string('nama_bapak_mertua')->nullable();

            $table->string('pekerjaan_bapak_mertua')->nullable();

            $table->string('nama_ibu_mertua')->nullable();

            $table->string('pekerjaan_ibu_mertua')->nullable();

            $table->string('kerabat_bekerja_diinstansi')->nullable();

            $table->string('tlpn_darurat')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->foreign('deleted_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        /*
        |--------------------------------------------------------------------------
        | Tabel data_saudara_kandung
        |--------------------------------------------------------------------------
        */
        Schema::create('data_saudara_kandung', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('data_riwayat_diri_id')
                ->constrained('data_riwayat_diri')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignUuid('data_riwayat_keluarga_id')
                ->constrained('data_riwayat_keluarga')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('nama_saudara_kandung')->nullable();

            $table->string('pekerjaan')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->foreign('deleted_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        /*
        |--------------------------------------------------------------------------
        | Tabel data_saudara_ipar
        |--------------------------------------------------------------------------
        */
        Schema::create('data_saudara_ipar', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('data_riwayat_diri_id')
                ->constrained('data_riwayat_diri')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignUuid('data_riwayat_keluarga_id')
                ->constrained('data_riwayat_keluarga')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('nama_saudara_ipar')->nullable();

            $table->string('pekerjaan')->nullable();

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
        Schema::dropIfExists('data_saudara_ipar');
        Schema::dropIfExists('data_saudara_kandung');
        Schema::dropIfExists('data_riwayat_keluarga');
    }
};