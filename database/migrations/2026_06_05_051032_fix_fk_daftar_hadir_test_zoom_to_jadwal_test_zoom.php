<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('daftar_hadir_test_zoom')) {
            return;
        }

        if (!Schema::hasColumn('daftar_hadir_test_zoom', 'jadwal_test_zoom_id')) {
            return;
        }

        DB::statement('
            ALTER TABLE daftar_hadir_test_zoom
            DROP CONSTRAINT IF EXISTS daftar_hadir_test_zoom_jadwal_test_zoom_id_foreign
        ');

        DB::statement('
            ALTER TABLE daftar_hadir_test_zoom
            DROP CONSTRAINT IF EXISTS daftar_hadir_test_zoom_jadwal_test_zoom_fk
        ');

        DB::statement('
            ALTER TABLE daftar_hadir_test_zoom
            ADD CONSTRAINT daftar_hadir_test_zoom_jadwal_test_zoom_fk
            FOREIGN KEY (jadwal_test_zoom_id)
            REFERENCES jadwal_test_zoom(id)
            ON DELETE CASCADE
        ');
    }

    public function down(): void
    {
        if (!Schema::hasTable('daftar_hadir_test_zoom')) {
            return;
        }

        if (!Schema::hasColumn('daftar_hadir_test_zoom', 'jadwal_test_zoom_id')) {
            return;
        }

        DB::statement('
            ALTER TABLE daftar_hadir_test_zoom
            DROP CONSTRAINT IF EXISTS daftar_hadir_test_zoom_jadwal_test_zoom_fk
        ');

        DB::statement('
            ALTER TABLE daftar_hadir_test_zoom
            ADD CONSTRAINT daftar_hadir_test_zoom_jadwal_test_zoom_id_foreign
            FOREIGN KEY (jadwal_test_zoom_id)
            REFERENCES jadwal_test_zoom(id)
            ON DELETE RESTRICT
        ');
    }
};