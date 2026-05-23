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
                CHECK (kehadiran IN ('hadir', 'tidak_hadir') OR kehadiran IS NULL)
            ");

            return;
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
            ALTER COLUMN kehadiran TYPE VARCHAR(20)
            USING CASE
                WHEN kehadiran::TEXT IN ('1', 'true', 't') THEN 'hadir'
                WHEN kehadiran::TEXT IN ('0', 'false', 'f') THEN 'tidak_hadir'
                WHEN kehadiran::TEXT IN ('hadir', 'tidak_hadir') THEN kehadiran::TEXT
                ELSE NULL
            END
        ");

        DB::statement("
            ALTER TABLE jadwal_test_zoom
            ALTER COLUMN kehadiran DROP NOT NULL
        ");

        DB::statement("
            ALTER TABLE jadwal_test_zoom
            ADD CONSTRAINT jadwal_test_zoom_kehadiran_check
            CHECK (kehadiran IN ('hadir', 'tidak_hadir') OR kehadiran IS NULL)
        ");
    }

    public function down(): void
    {
        if (!Schema::hasColumn('jadwal_test_zoom', 'kehadiran')) {
            return;
        }

        DB::statement("
            ALTER TABLE jadwal_test_zoom
            DROP CONSTRAINT IF EXISTS jadwal_test_zoom_kehadiran_check
        ");

        DB::statement("
            ALTER TABLE jadwal_test_zoom
            ALTER COLUMN kehadiran TYPE BOOLEAN
            USING CASE
                WHEN kehadiran = 'hadir' THEN TRUE
                WHEN kehadiran = 'tidak_hadir' THEN FALSE
                ELSE NULL
            END
        ");

        DB::statement("
            ALTER TABLE jadwal_test_zoom
            ALTER COLUMN kehadiran SET DEFAULT FALSE
        ");
    }
};