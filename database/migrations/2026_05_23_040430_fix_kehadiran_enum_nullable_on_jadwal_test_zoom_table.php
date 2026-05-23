<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('jadwal_test_zoom', 'kehadiran')) {
            DB::statement("
                ALTER TABLE jadwal_test_zoom
                ADD COLUMN kehadiran VARCHAR(20) NULL
            ");
        }

        DB::statement("
            ALTER TABLE jadwal_test_zoom
            DROP CONSTRAINT IF EXISTS jadwal_test_zoom_kehadiran_check
        ");

        DB::statement("
            ALTER TABLE jadwal_test_zoom
            ALTER COLUMN kehadiran DROP DEFAULT
        ");

        DB::statement("
            ALTER TABLE jadwal_test_zoom
            ALTER COLUMN kehadiran DROP NOT NULL
        ");

        DB::statement("
            ALTER TABLE jadwal_test_zoom
            ALTER COLUMN kehadiran TYPE VARCHAR(20)
            USING CASE
                WHEN kehadiran::TEXT IN ('hadir') THEN 'hadir'
                WHEN kehadiran::TEXT IN ('tidak_hadir') THEN 'tidak_hadir'
                ELSE NULL
            END
        ");

        DB::statement("
            UPDATE jadwal_test_zoom
            SET kehadiran = NULL
            WHERE kehadiran IS NULL
               OR kehadiran NOT IN ('hadir', 'tidak_hadir')
        ");

        DB::statement("
            ALTER TABLE jadwal_test_zoom
            ADD CONSTRAINT jadwal_test_zoom_kehadiran_check
            CHECK (
                kehadiran IS NULL
                OR kehadiran IN ('hadir', 'tidak_hadir')
            )
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE jadwal_test_zoom
            DROP CONSTRAINT IF EXISTS jadwal_test_zoom_kehadiran_check
        ");

        DB::statement("
            ALTER TABLE jadwal_test_zoom
            ALTER COLUMN kehadiran DROP DEFAULT
        ");

        DB::statement("
            ALTER TABLE jadwal_test_zoom
            ALTER COLUMN kehadiran DROP NOT NULL
        ");
    }
};