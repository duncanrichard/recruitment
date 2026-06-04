<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE data_riwayat_kesehatan
            DROP CONSTRAINT IF EXISTS data_riwayat_kesehatan_buta_warna_check
        ");

        DB::statement("
            ALTER TABLE data_riwayat_kesehatan
            ADD CONSTRAINT data_riwayat_kesehatan_buta_warna_check
            CHECK (
                buta_warna IS NULL
                OR buta_warna IN (
                    'Ya',
                    'Tidak',
                    'Buta Warna Total',
                    'Buta Warna Partial',
                    'Ya, Buta Warna Total',
                    'Ya, Buta Warna Partial / Sebagian'
                )
            )
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE data_riwayat_kesehatan
            DROP CONSTRAINT IF EXISTS data_riwayat_kesehatan_buta_warna_check
        ");

        DB::statement("
            ALTER TABLE data_riwayat_kesehatan
            ADD CONSTRAINT data_riwayat_kesehatan_buta_warna_check
            CHECK (
                buta_warna IS NULL
                OR buta_warna IN (
                    'Ya',
                    'Tidak'
                )
            )
        ");
    }
};