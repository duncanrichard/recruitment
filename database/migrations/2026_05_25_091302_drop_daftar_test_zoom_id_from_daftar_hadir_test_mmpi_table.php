<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('daftar_hadir_test_mmpi')) {
            return;
        }

        DB::statement("
            ALTER TABLE daftar_hadir_test_mmpi
            DROP CONSTRAINT IF EXISTS daftar_hadir_test_mmpi_daftar_test_zoom_id_foreign
        ");

        DB::statement("
            ALTER TABLE daftar_hadir_test_mmpi
            DROP CONSTRAINT IF EXISTS dh_mmpi_daftar_test_zoom_fk
        ");

        DB::statement("
            DROP INDEX IF EXISTS daftar_hadir_test_mmpi_daftar_test_zoom_id_index
        ");

        DB::statement("
            DROP INDEX IF EXISTS dh_mmpi_daftar_test_zoom_idx
        ");

        Schema::table('daftar_hadir_test_mmpi', function (Blueprint $table) {
            if (Schema::hasColumn('daftar_hadir_test_mmpi', 'daftar_test_zoom_id')) {
                $table->dropColumn('daftar_test_zoom_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('daftar_hadir_test_mmpi')) {
            return;
        }

        Schema::table('daftar_hadir_test_mmpi', function (Blueprint $table) {
            if (!Schema::hasColumn('daftar_hadir_test_mmpi', 'daftar_test_zoom_id')) {
                $table->uuid('daftar_test_zoom_id')->nullable()->after('id');
            }
        });
    }
};