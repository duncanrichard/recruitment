<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('
            ALTER TABLE data_riwayat_kesehatan
            DROP CONSTRAINT IF EXISTS data_riwayat_kesehatan_menulis_dengan_tangan_check
        ');

        DB::statement("
            ALTER TABLE data_riwayat_kesehatan
            ADD CONSTRAINT data_riwayat_kesehatan_menulis_dengan_tangan_check
            CHECK (
                menulis_dengan_tangan IS NULL
                OR menulis_dengan_tangan IN ('Kanan', 'Kiri')
            )
        ");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('
            ALTER TABLE data_riwayat_kesehatan
            DROP CONSTRAINT IF EXISTS data_riwayat_kesehatan_menulis_dengan_tangan_check
        ');

        DB::statement("
            ALTER TABLE data_riwayat_kesehatan
            ADD CONSTRAINT data_riwayat_kesehatan_menulis_dengan_tangan_check
            CHECK (
                menulis_dengan_tangan IS NULL
                OR menulis_dengan_tangan IN ('Ya', 'Tidak')
            )
        ");
    }
};
