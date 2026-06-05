<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('jadwal_test_zoom', function (Blueprint $table) {
            if (!Schema::hasColumn('jadwal_test_zoom', 'sesi')) {
                $table->string('sesi')->nullable()->after('data_riwayat_diri_id');
            }

            if (!Schema::hasColumn('jadwal_test_zoom', 'jadwal_mulai')) {
                $table->dateTime('jadwal_mulai')->nullable()->after('sesi');
            }

            if (!Schema::hasColumn('jadwal_test_zoom', 'jadwal_selesai')) {
                $table->dateTime('jadwal_selesai')->nullable()->after('jadwal_mulai');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal_test_zoom', function (Blueprint $table) {
            if (Schema::hasColumn('jadwal_test_zoom', 'jadwal_selesai')) {
                $table->dropColumn('jadwal_selesai');
            }

            if (Schema::hasColumn('jadwal_test_zoom', 'jadwal_mulai')) {
                $table->dropColumn('jadwal_mulai');
            }

            if (Schema::hasColumn('jadwal_test_zoom', 'sesi')) {
                $table->dropColumn('sesi');
            }
        });
    }
};