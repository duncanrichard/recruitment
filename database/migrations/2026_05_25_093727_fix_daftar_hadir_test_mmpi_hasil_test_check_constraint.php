<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('daftar_hadir_test_mmpi')) {
            return;
        }

        DB::statement("
            UPDATE daftar_hadir_test_mmpi
            SET hasil_test = LOWER(TRIM(hasil_test))
            WHERE hasil_test IS NOT NULL
        ");

        DB::statement("
            UPDATE daftar_hadir_test_mmpi
            SET hasil_test = NULL
            WHERE hasil_test IS NOT NULL
              AND LOWER(TRIM(hasil_test)) NOT IN ('lolos', 'gagal')
        ");

        DB::statement("
            ALTER TABLE daftar_hadir_test_mmpi
            DROP CONSTRAINT IF EXISTS daftar_hadir_test_mmpi_hasil_test_check
        ");

        DB::statement("
            ALTER TABLE daftar_hadir_test_mmpi
            DROP CONSTRAINT IF EXISTS dh_mmpi_hasil_test_check
        ");

        DB::statement("
            ALTER TABLE daftar_hadir_test_mmpi
            ADD CONSTRAINT daftar_hadir_test_mmpi_hasil_test_check
            CHECK (
                hasil_test IS NULL
                OR hasil_test IN ('lolos', 'gagal')
            )
        ");
    }

    public function down(): void
    {
        if (!Schema::hasTable('daftar_hadir_test_mmpi')) {
            return;
        }

        DB::statement("
            ALTER TABLE daftar_hadir_test_mmpi
            DROP CONSTRAINT IF EXISTS daftar_hadir_test_mmpi_hasil_test_check
        ");
    }
};