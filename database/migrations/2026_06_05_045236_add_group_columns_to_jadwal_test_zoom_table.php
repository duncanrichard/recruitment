<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_test_zoom', function (Blueprint $table) {
            if (!Schema::hasColumn('jadwal_test_zoom', 'group_key')) {
                $table->string('group_key')->nullable()->index();
            }

            if (!Schema::hasColumn('jadwal_test_zoom', 'sesi')) {
                $table->string('sesi', 100)->nullable();
            }

            if (!Schema::hasColumn('jadwal_test_zoom', 'jadwal_mulai')) {
                $table->timestamp('jadwal_mulai')->nullable();
            }

            if (!Schema::hasColumn('jadwal_test_zoom', 'jadwal_selesai')) {
                $table->timestamp('jadwal_selesai')->nullable();
            }

            if (!Schema::hasColumn('jadwal_test_zoom', 'link_zoom')) {
                $table->string('link_zoom', 2048)->nullable();
            }

            if (!Schema::hasColumn('jadwal_test_zoom', 'kehadiran')) {
                $table->string('kehadiran', 50)->nullable();
            }
        });

        DB::statement("
            UPDATE jadwal_test_zoom
            SET sesi = COALESCE(NULLIF(sesi, ''), 'Sesi 1')
            WHERE sesi IS NULL OR sesi = ''
        ");

        DB::statement("
            UPDATE jadwal_test_zoom
            SET jadwal_mulai = COALESCE(jadwal_mulai, jadwal)
            WHERE jadwal_mulai IS NULL
        ");

        DB::statement("
            UPDATE jadwal_test_zoom
            SET jadwal_selesai = COALESCE(jadwal_selesai, jadwal_mulai, jadwal)
            WHERE jadwal_selesai IS NULL
        ");

        DB::statement("
            UPDATE jadwal_test_zoom
            SET group_key =
                lower(regexp_replace(COALESCE(NULLIF(sesi, ''), 'Sesi 1'), '[^a-zA-Z0-9]+', '-', 'g'))
                || '_' ||
                to_char(COALESCE(jadwal_mulai, jadwal), 'YYYYMMDDHH24MISS')
                || '_' ||
                to_char(COALESCE(jadwal_selesai, jadwal_mulai, jadwal), 'YYYYMMDDHH24MISS')
            WHERE group_key IS NULL OR group_key = ''
        ");
    }

    public function down(): void
    {
        Schema::table('jadwal_test_zoom', function (Blueprint $table) {
            if (Schema::hasColumn('jadwal_test_zoom', 'group_key')) {
                $table->dropColumn('group_key');
            }

            if (Schema::hasColumn('jadwal_test_zoom', 'sesi')) {
                $table->dropColumn('sesi');
            }

            if (Schema::hasColumn('jadwal_test_zoom', 'jadwal_mulai')) {
                $table->dropColumn('jadwal_mulai');
            }

            if (Schema::hasColumn('jadwal_test_zoom', 'jadwal_selesai')) {
                $table->dropColumn('jadwal_selesai');
            }

            if (Schema::hasColumn('jadwal_test_zoom', 'link_zoom')) {
                $table->dropColumn('link_zoom');
            }

            if (Schema::hasColumn('jadwal_test_zoom', 'kehadiran')) {
                $table->dropColumn('kehadiran');
            }
        });
    }
};