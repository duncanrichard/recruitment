<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            UPDATE daftar_hadir_test_zoom
            SET hasil_test = LOWER(TRIM(hasil_test))
            WHERE hasil_test IS NOT NULL
        ");

        DB::statement("
            UPDATE daftar_hadir_test_zoom
            SET hasil_test = NULL
            WHERE hasil_test IS NOT NULL
              AND LOWER(TRIM(hasil_test)) NOT IN ('lolos', 'gagal')
        ");

        DB::statement("
            ALTER TABLE daftar_hadir_test_zoom
            DROP CONSTRAINT IF EXISTS daftar_hadir_test_zoom_hasil_test_check
        ");

        DB::statement("
            ALTER TABLE daftar_hadir_test_zoom
            ADD CONSTRAINT daftar_hadir_test_zoom_hasil_test_check
            CHECK (
                hasil_test IS NULL
                OR hasil_test IN ('lolos', 'gagal')
            )
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE daftar_hadir_test_zoom
            DROP CONSTRAINT IF EXISTS daftar_hadir_test_zoom_hasil_test_check
        ");
    }
};