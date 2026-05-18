<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE data_riwayat_diri
            DROP CONSTRAINT IF EXISTS data_riwayat_diri_str_aktif_check
        ");

        DB::statement("
            ALTER TABLE data_riwayat_diri
            ALTER COLUMN str_aktif DROP DEFAULT
        ");

        DB::statement("
            ALTER TABLE data_riwayat_diri
            ALTER COLUMN str_aktif DROP NOT NULL
        ");

        DB::statement("
            ALTER TABLE data_riwayat_diri
            ADD CONSTRAINT data_riwayat_diri_str_aktif_check
            CHECK (
                str_aktif IS NULL
                OR str_aktif IN ('active', 'non_active')
            )
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE data_riwayat_diri
            DROP CONSTRAINT IF EXISTS data_riwayat_diri_str_aktif_check
        ");

        DB::statement("
            ALTER TABLE data_riwayat_diri
            ALTER COLUMN str_aktif SET DEFAULT 'active'
        ");

        DB::statement("
            ALTER TABLE data_riwayat_diri
            ADD CONSTRAINT data_riwayat_diri_str_aktif_check
            CHECK (
                str_aktif IS NULL
                OR str_aktif IN ('active', 'non_active')
            )
        ");
    }
};