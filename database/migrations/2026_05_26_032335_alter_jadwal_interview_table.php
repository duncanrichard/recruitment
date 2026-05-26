<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_interview', function (Blueprint $table) {
            if (!Schema::hasColumn('jadwal_interview', 'judul_interview')) {
                $table->string('judul_interview')->nullable()->after('id');
            }

            if (Schema::hasColumn('jadwal_interview', 'data_riwayat_diri_id')) {
                $table->dropColumn('data_riwayat_diri_id');
            }

            if (Schema::hasColumn('jadwal_interview', 'nama_panelis')) {
                $table->dropColumn('nama_panelis');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_interview', function (Blueprint $table) {
            if (!Schema::hasColumn('jadwal_interview', 'data_riwayat_diri_id')) {
                $table->uuid('data_riwayat_diri_id')->nullable();
            }

            if (!Schema::hasColumn('jadwal_interview', 'nama_panelis')) {
                $table->string('nama_panelis')->nullable();
            }

            if (Schema::hasColumn('jadwal_interview', 'judul_interview')) {
                $table->dropColumn('judul_interview');
            }
        });
    }
};