<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            UPDATE data_riwayat_diri
            SET str_aktif = CASE
                WHEN str_aktif = 'Y' THEN 'aktif'
                WHEN str_aktif = 'Tidak' THEN 'tidak aktif'
                WHEN str_aktif = 'aktif' THEN 'aktif'
                WHEN str_aktif = 'tidak aktif' THEN 'tidak aktif'
                ELSE 'aktif'
            END
        ");

        DB::statement("
            ALTER TABLE data_riwayat_diri
            ALTER COLUMN str_aktif TYPE VARCHAR(20)
        ");

        DB::statement("
            ALTER TABLE data_riwayat_diri
            ALTER COLUMN str_aktif SET DEFAULT 'aktif'
        ");

        DB::statement("
            ALTER TABLE data_riwayat_diri
            DROP CONSTRAINT IF EXISTS data_riwayat_diri_str_aktif_check
        ");

        DB::statement("
            ALTER TABLE data_riwayat_diri
            ADD CONSTRAINT data_riwayat_diri_str_aktif_check
            CHECK (str_aktif IN ('aktif', 'tidak aktif'))
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE data_riwayat_diri
            DROP CONSTRAINT IF EXISTS data_riwayat_diri_str_aktif_check
        ");

        DB::statement("
            UPDATE data_riwayat_diri
            SET str_aktif = CASE
                WHEN str_aktif = 'aktif' THEN 'Y'
                WHEN str_aktif = 'tidak aktif' THEN 'Tidak'
                ELSE 'Y'
            END
        ");

        DB::statement("
            ALTER TABLE data_riwayat_diri
            ALTER COLUMN str_aktif SET DEFAULT 'Y'
        ");

        DB::statement("
            ALTER TABLE data_riwayat_diri
            ADD CONSTRAINT data_riwayat_diri_str_aktif_check
            CHECK (str_aktif IN ('Y', 'Tidak'))
        ");
    }
};