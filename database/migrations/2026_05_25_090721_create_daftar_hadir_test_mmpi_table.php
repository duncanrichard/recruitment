<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('daftar_hadir_test_mmpi')) {
            Schema::create('daftar_hadir_test_mmpi', function (Blueprint $table) {
                $table->uuid('id')->primary();

                $table->uuid('jadwal_test_mmpi_id');
                $table->uuid('data_riwayat_diri_id');

                $table->date('tanggal_kehadiran')->nullable();

                $table->string('status_kehadiran', 50)->nullable();
                $table->string('hasil_test', 50)->nullable();

                $table->timestamps();
                $table->softDeletes();

                $table->foreign('jadwal_test_mmpi_id', 'dh_mmpi_jadwal_test_mmpi_fk')
                    ->references('id')
                    ->on('jadwal_test_mmpi')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();

                $table->foreign('data_riwayat_diri_id', 'dh_mmpi_riwayat_diri_fk')
                    ->references('id')
                    ->on('data_riwayat_diri')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();

                $table->unique(
                    ['jadwal_test_mmpi_id', 'data_riwayat_diri_id'],
                    'dh_mmpi_unique_jadwal_peserta'
                );

                $table->index('jadwal_test_mmpi_id', 'dh_mmpi_jadwal_idx');
                $table->index('data_riwayat_diri_id', 'dh_mmpi_riwayat_idx');
                $table->index('tanggal_kehadiran', 'dh_mmpi_tanggal_idx');
                $table->index('status_kehadiran', 'dh_mmpi_status_idx');
                $table->index('hasil_test', 'dh_mmpi_hasil_idx');
            });
        }

        DB::statement("
            ALTER TABLE daftar_hadir_test_mmpi
            ADD CONSTRAINT dh_mmpi_status_kehadiran_check
            CHECK (
                status_kehadiran IS NULL
                OR status_kehadiran IN ('hadir', 'tidak_hadir')
            )
        ");

        DB::statement("
            ALTER TABLE daftar_hadir_test_mmpi
            ADD CONSTRAINT dh_mmpi_hasil_test_check
            CHECK (
                hasil_test IS NULL
                OR hasil_test IN ('lolos', 'gagal')
            )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('daftar_hadir_test_mmpi');
    }
};