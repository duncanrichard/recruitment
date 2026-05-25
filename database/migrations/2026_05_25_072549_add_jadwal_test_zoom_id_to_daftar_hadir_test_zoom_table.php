<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daftar_hadir_test_zoom', function (Blueprint $table) {
            if (!Schema::hasColumn('daftar_hadir_test_zoom', 'jadwal_test_zoom_id')) {
                $table->uuid('jadwal_test_zoom_id')
                    ->nullable()
                    ->after('data_riwayat_diri_id');
            }
        });

        /*
        |--------------------------------------------------------------------------
        | Backfill Data Lama
        |--------------------------------------------------------------------------
        | Jika sebelumnya daftar hadir hanya punya data_riwayat_diri_id dan
        | tanggal_kehadiran, maka kolom jadwal_test_zoom_id akan otomatis
        | dicocokkan ke jadwal_test_zoom berdasarkan:
        | - data_riwayat_diri_id sama
        | - tanggal_kehadiran sama dengan DATE(jadwal)
        |
        | Aman untuk PostgreSQL.
        */
        DB::statement("
            UPDATE daftar_hadir_test_zoom AS hadir
            SET jadwal_test_zoom_id = jadwal.id
            FROM jadwal_test_zoom AS jadwal
            WHERE hadir.jadwal_test_zoom_id IS NULL
              AND hadir.data_riwayat_diri_id = jadwal.data_riwayat_diri_id
              AND hadir.tanggal_kehadiran IS NOT NULL
              AND jadwal.jadwal IS NOT NULL
              AND hadir.tanggal_kehadiran = DATE(jadwal.jadwal)
        ");

        Schema::table('daftar_hadir_test_zoom', function (Blueprint $table) {
            $table->foreign('jadwal_test_zoom_id', 'daftar_hadir_test_zoom_jadwal_fk')
                ->references('id')
                ->on('jadwal_test_zoom')
                ->nullOnDelete();
        });

        Schema::table('daftar_hadir_test_zoom', function (Blueprint $table) {
            $table->index(
                ['jadwal_test_zoom_id'],
                'daftar_hadir_test_zoom_jadwal_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('daftar_hadir_test_zoom', function (Blueprint $table) {
            $table->dropForeign('daftar_hadir_test_zoom_jadwal_fk');
            $table->dropIndex('daftar_hadir_test_zoom_jadwal_idx');
            $table->dropColumn('jadwal_test_zoom_id');
        });
    }
};