<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('data_riwayat_pekerjaan')) {
            return;
        }

        if (!Schema::hasColumn('data_riwayat_pekerjaan', 'gaji_terakhir')) {
            return;
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("
                ALTER TABLE data_riwayat_pekerjaan
                ALTER COLUMN gaji_terakhir TYPE DECIMAL(20,2)
                USING gaji_terakhir::DECIMAL(20,2)
            ");

            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE data_riwayat_pekerjaan
                MODIFY gaji_terakhir DECIMAL(20,2) NULL
            ");

            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            // SQLite tidak mendukung alter column type dengan aman.
            return;
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('data_riwayat_pekerjaan')) {
            return;
        }

        if (!Schema::hasColumn('data_riwayat_pekerjaan', 'gaji_terakhir')) {
            return;
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("
                ALTER TABLE data_riwayat_pekerjaan
                ALTER COLUMN gaji_terakhir TYPE DECIMAL(15,2)
                USING LEAST(gaji_terakhir, 9999999999999.99)::DECIMAL(15,2)
            ");

            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE data_riwayat_pekerjaan
                MODIFY gaji_terakhir DECIMAL(15,2) NULL
            ");

            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            return;
        }
    }
};
