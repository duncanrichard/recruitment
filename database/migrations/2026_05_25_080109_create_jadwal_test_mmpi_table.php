<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_test_mmpi', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('daftar_hadir_test_zoom_id');
            $table->uuid('data_riwayat_diri_id');

            $table->date('tanggal');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('daftar_hadir_test_zoom_id', 'jadwal_mmpi_daftar_hadir_zoom_fk')
                ->references('id')
                ->on('daftar_hadir_test_zoom')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('data_riwayat_diri_id', 'jadwal_mmpi_riwayat_diri_fk')
                ->references('id')
                ->on('data_riwayat_diri')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->unique(
                ['daftar_hadir_test_zoom_id', 'data_riwayat_diri_id'],
                'jadwal_mmpi_unique_peserta_zoom'
            );

            $table->index('tanggal', 'jadwal_mmpi_tanggal_idx');
            $table->index('daftar_hadir_test_zoom_id', 'jadwal_mmpi_daftar_hadir_idx');
            $table->index('data_riwayat_diri_id', 'jadwal_mmpi_riwayat_diri_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_test_mmpi');
    }
};