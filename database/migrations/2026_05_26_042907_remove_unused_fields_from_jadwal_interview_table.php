<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_interview', function (Blueprint $table) {
            if (Schema::hasColumn('jadwal_interview', 'daftar_test_mmpi')) {
                $table->dropColumn('daftar_test_mmpi');
            }

            if (Schema::hasColumn('jadwal_interview', 'status_kehadiran')) {
                $table->dropColumn('status_kehadiran');
            }

            if (Schema::hasColumn('jadwal_interview', 'hasil_interview')) {
                $table->dropColumn('hasil_interview');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_interview', function (Blueprint $table) {
            if (!Schema::hasColumn('jadwal_interview', 'daftar_test_mmpi')) {
                $table->uuid('daftar_test_mmpi')->nullable();
            }

            if (!Schema::hasColumn('jadwal_interview', 'status_kehadiran')) {
                $table->string('status_kehadiran')->nullable();
            }

            if (!Schema::hasColumn('jadwal_interview', 'hasil_interview')) {
                $table->string('hasil_interview')->nullable();
            }
        });
    }
};