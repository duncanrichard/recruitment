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
        | 1. daftar_hadir_test_zoom
        |--------------------------------------------------------------------------
        */
        Schema::create('daftar_hadir_test_zoom', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('data_riwayat_diri_id')
                ->constrained('data_riwayat_diri')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->date('tanggal_kehadiran')->nullable();

            $table->enum('status_kehadiran', [
                'hadir',
                'Tidak hadir',
                'Tidak Respon',
            ])->nullable();

            $table->enum('hasil_test', [
                'Belum Test',
                'Lolos',
                'Tidak Lolos',
                'lolos Dipertimbangkan',
            ])->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->uuid('deleted_by')->nullable();
        });

        /*
        |--------------------------------------------------------------------------
        | 2. daftar_hadir_test_mmpi
        |--------------------------------------------------------------------------
        */
        Schema::create('daftar_hadir_test_mmpi', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('data_riwayat_diri_id')
                ->constrained('data_riwayat_diri')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignUuid('daftar_test_zoom_id')
                ->constrained('daftar_hadir_test_zoom')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->date('tanggal_kehadiran')->nullable();

            $table->enum('status_kehadiran', [
                'hadir',
                'Tidak hadir',
                'Tidak Respon',
            ])->nullable();

            $table->enum('hasil_test', [
                'Belum Test',
                'Lolos',
                'Tidak Lolos',
                'lolos Dipertimbangkan',
            ])->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->uuid('deleted_by')->nullable();
        });

        /*
        |--------------------------------------------------------------------------
        | 3. jadwal_interview
        |--------------------------------------------------------------------------
        */
        Schema::create('jadwal_interview', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('data_riwayat_diri_id')
                ->constrained('data_riwayat_diri')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignUuid('daftar_test_mmpi')
                ->constrained('daftar_hadir_test_mmpi')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->dateTime('jadwal_interview')->nullable();

            $table->enum('status_kehadiran', [
                'hadir',
                'Tidak hadir',
                'Tidak Respon',
            ])->nullable();

            $table->enum('hasil_interview', [
                'Lolos',
                'Tidak Lolos',
                'lolos Dipertimbangkan',
            ])->nullable();

            $table->string('nama_panelis')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->uuid('deleted_by')->nullable();
        });

        /*
        |--------------------------------------------------------------------------
        | 4. status_pengajuan_dirut
        |--------------------------------------------------------------------------
        */
        Schema::create('status_pengajuan_dirut', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('data_riwayat_diri_id')
                ->constrained('data_riwayat_diri')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignUuid('interview_id')
                ->constrained('jadwal_interview')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->enum('status', [
                'Lolos',
                'Tidak Lolos',
                'lolos Dipertimbangkan',
            ])->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->uuid('deleted_by')->nullable();
        });

        /*
        |--------------------------------------------------------------------------
        | 5. background_chaking
        |--------------------------------------------------------------------------
        */
        Schema::create('background_chaking', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('data_riwayat_diri_id')
                ->constrained('data_riwayat_diri')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignUuid('pengajuan_dirut_id')
                ->constrained('status_pengajuan_dirut')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->enum('status', [
                'Lolos',
                'Tidak Lolos',
                'lolos Dipertimbangkan',
            ])->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->uuid('deleted_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('background_chaking');
        Schema::dropIfExists('status_pengajuan_dirut');
        Schema::dropIfExists('jadwal_interview');
        Schema::dropIfExists('daftar_hadir_test_mmpi');
        Schema::dropIfExists('daftar_hadir_test_zoom');
    }
};