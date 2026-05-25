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
                $table->timestamps();
                $table->softDeletes();
            });
        }

        Schema::table('daftar_hadir_test_mmpi', function (Blueprint $table) {
            if (!Schema::hasColumn('daftar_hadir_test_mmpi', 'jadwal_test_mmpi_id')) {
                $table->uuid('jadwal_test_mmpi_id')->nullable()->after('id');
            }

            if (!Schema::hasColumn('daftar_hadir_test_mmpi', 'data_riwayat_diri_id')) {
                $table->uuid('data_riwayat_diri_id')->nullable()->after('jadwal_test_mmpi_id');
            }

            if (!Schema::hasColumn('daftar_hadir_test_mmpi', 'tanggal_kehadiran')) {
                $table->date('tanggal_kehadiran')->nullable()->after('data_riwayat_diri_id');
            }

            if (!Schema::hasColumn('daftar_hadir_test_mmpi', 'status_kehadiran')) {
                $table->string('status_kehadiran', 50)->nullable()->after('tanggal_kehadiran');
            }

            if (!Schema::hasColumn('daftar_hadir_test_mmpi', 'hasil_test')) {
                $table->string('hasil_test', 50)->nullable()->after('status_kehadiran');
            }

            if (!Schema::hasColumn('daftar_hadir_test_mmpi', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        DB::statement("
            ALTER TABLE daftar_hadir_test_mmpi
            DROP CONSTRAINT IF EXISTS dh_mmpi_jadwal_test_mmpi_fk
        ");

        DB::statement("
            ALTER TABLE daftar_hadir_test_mmpi
            DROP CONSTRAINT IF EXISTS dh_mmpi_riwayat_diri_fk
        ");

        DB::statement("
            ALTER TABLE daftar_hadir_test_mmpi
            DROP CONSTRAINT IF EXISTS dh_mmpi_unique_jadwal_peserta
        ");

        DB::statement("
            ALTER TABLE daftar_hadir_test_mmpi
            DROP CONSTRAINT IF EXISTS dh_mmpi_status_kehadiran_check
        ");

        DB::statement("
            ALTER TABLE daftar_hadir_test_mmpi
            DROP CONSTRAINT IF EXISTS dh_mmpi_hasil_test_check
        ");

        DB::statement("
            ALTER TABLE daftar_hadir_test_mmpi
            ADD CONSTRAINT dh_mmpi_jadwal_test_mmpi_fk
            FOREIGN KEY (jadwal_test_mmpi_id)
            REFERENCES jadwal_test_mmpi(id)
            ON UPDATE CASCADE
            ON DELETE RESTRICT
        ");

        DB::statement("
            ALTER TABLE daftar_hadir_test_mmpi
            ADD CONSTRAINT dh_mmpi_riwayat_diri_fk
            FOREIGN KEY (data_riwayat_diri_id)
            REFERENCES data_riwayat_diri(id)
            ON UPDATE CASCADE
            ON DELETE RESTRICT
        ");

        DB::statement("
            ALTER TABLE daftar_hadir_test_mmpi
            ADD CONSTRAINT dh_mmpi_unique_jadwal_peserta
            UNIQUE (jadwal_test_mmpi_id, data_riwayat_diri_id)
        ");

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

        DB::statement("
            CREATE INDEX IF NOT EXISTS dh_mmpi_jadwal_idx
            ON daftar_hadir_test_mmpi(jadwal_test_mmpi_id)
        ");

        DB::statement("
            CREATE INDEX IF NOT EXISTS dh_mmpi_riwayat_idx
            ON daftar_hadir_test_mmpi(data_riwayat_diri_id)
        ");

        DB::statement("
            CREATE INDEX IF NOT EXISTS dh_mmpi_tanggal_idx
            ON daftar_hadir_test_mmpi(tanggal_kehadiran)
        ");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE daftar_hadir_test_mmpi DROP CONSTRAINT IF EXISTS dh_mmpi_jadwal_test_mmpi_fk");
        DB::statement("ALTER TABLE daftar_hadir_test_mmpi DROP CONSTRAINT IF EXISTS dh_mmpi_riwayat_diri_fk");
        DB::statement("ALTER TABLE daftar_hadir_test_mmpi DROP CONSTRAINT IF EXISTS dh_mmpi_unique_jadwal_peserta");
        DB::statement("ALTER TABLE daftar_hadir_test_mmpi DROP CONSTRAINT IF EXISTS dh_mmpi_status_kehadiran_check");
        DB::statement("ALTER TABLE daftar_hadir_test_mmpi DROP CONSTRAINT IF EXISTS dh_mmpi_hasil_test_check");
    }
};