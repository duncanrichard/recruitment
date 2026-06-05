<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('jadwal_test_mmpi')) {
            return;
        }

        DB::statement('
            ALTER TABLE jadwal_test_mmpi
            DROP CONSTRAINT IF EXISTS jadwal_mmpi_unique_peserta_zoom
        ');

        DB::statement('
            DROP INDEX IF EXISTS jadwal_mmpi_unique_peserta_zoom
        ');

        DB::statement('
            DROP INDEX IF EXISTS jadwal_mmpi_unique_peserta_zoom_active
        ');

        DB::statement('
            CREATE UNIQUE INDEX jadwal_mmpi_unique_peserta_zoom_active
            ON jadwal_test_mmpi (daftar_hadir_test_zoom_id, data_riwayat_diri_id)
            WHERE deleted_at IS NULL
        ');
    }

    public function down(): void
    {
        if (!Schema::hasTable('jadwal_test_mmpi')) {
            return;
        }

        DB::statement('
            DROP INDEX IF EXISTS jadwal_mmpi_unique_peserta_zoom_active
        ');

        DB::statement('
            ALTER TABLE jadwal_test_mmpi
            ADD CONSTRAINT jadwal_mmpi_unique_peserta_zoom
            UNIQUE (daftar_hadir_test_zoom_id, data_riwayat_diri_id)
        ');
    }
};