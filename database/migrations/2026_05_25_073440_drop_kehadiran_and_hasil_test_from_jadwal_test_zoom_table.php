<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Pindahkan data lama ke daftar_hadir_test_zoom sebelum kolom dihapus
        |--------------------------------------------------------------------------
        | Jika sebelumnya sudah ada kehadiran / hasil_test di jadwal_test_zoom,
        | data akan diamankan ke tabel daftar_hadir_test_zoom.
        */
        if (
            Schema::hasTable('daftar_hadir_test_zoom') &&
            Schema::hasTable('jadwal_test_zoom') &&
            Schema::hasColumn('daftar_hadir_test_zoom', 'jadwal_test_zoom_id') &&
            Schema::hasColumn('daftar_hadir_test_zoom', 'data_riwayat_diri_id') &&
            Schema::hasColumn('daftar_hadir_test_zoom', 'tanggal_kehadiran') &&
            Schema::hasColumn('daftar_hadir_test_zoom', 'status_kehadiran') &&
            Schema::hasColumn('daftar_hadir_test_zoom', 'hasil_test')
        ) {
            DB::statement("
                INSERT INTO daftar_hadir_test_zoom (
                    id,
                    jadwal_test_zoom_id,
                    data_riwayat_diri_id,
                    tanggal_kehadiran,
                    status_kehadiran,
                    hasil_test,
                    created_at,
                    updated_at
                )
                SELECT
                    gen_random_uuid(),
                    j.id,
                    j.data_riwayat_diri_id,
                    DATE(j.jadwal),
                    j.kehadiran,
                    j.hasil_test,
                    COALESCE(j.created_at, NOW()),
                    NOW()
                FROM jadwal_test_zoom j
                WHERE j.deleted_at IS NULL
                  AND (
                        NULLIF(TRIM(COALESCE(j.kehadiran, '')), '') IS NOT NULL
                        OR NULLIF(TRIM(COALESCE(j.hasil_test, '')), '') IS NOT NULL
                  )
                  AND NOT EXISTS (
                        SELECT 1
                        FROM daftar_hadir_test_zoom d
                        WHERE d.jadwal_test_zoom_id = j.id
                          AND d.deleted_at IS NULL
                  )
            ");
        }

        Schema::table('jadwal_test_zoom', function (Blueprint $table) {
            if (Schema::hasColumn('jadwal_test_zoom', 'kehadiran')) {
                $table->dropColumn('kehadiran');
            }

            if (Schema::hasColumn('jadwal_test_zoom', 'hasil_test')) {
                $table->dropColumn('hasil_test');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_test_zoom', function (Blueprint $table) {
            if (!Schema::hasColumn('jadwal_test_zoom', 'kehadiran')) {
                $table->string('kehadiran', 20)->nullable()->after('deleted_at');
            }

            if (!Schema::hasColumn('jadwal_test_zoom', 'hasil_test')) {
                $table->string('hasil_test', 255)->nullable()->after('link_zoom');
            }
        });

        /*
        |--------------------------------------------------------------------------
        | Restore data dari daftar_hadir_test_zoom jika rollback
        |--------------------------------------------------------------------------
        */
        if (
            Schema::hasTable('daftar_hadir_test_zoom') &&
            Schema::hasColumn('daftar_hadir_test_zoom', 'jadwal_test_zoom_id') &&
            Schema::hasColumn('daftar_hadir_test_zoom', 'status_kehadiran') &&
            Schema::hasColumn('daftar_hadir_test_zoom', 'hasil_test')
        ) {
            DB::statement("
                UPDATE jadwal_test_zoom j
                SET
                    kehadiran = d.status_kehadiran,
                    hasil_test = d.hasil_test,
                    updated_at = NOW()
                FROM daftar_hadir_test_zoom d
                WHERE d.jadwal_test_zoom_id = j.id
                  AND d.deleted_at IS NULL
            ");
        }
    }
};