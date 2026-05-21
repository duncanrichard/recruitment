<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_riwayat_diri', function (Blueprint $table) {
            if (!Schema::hasColumn('data_riwayat_diri', 'provinsi_id')) {
                $table->string('provinsi_id', 50)->nullable()->after('alamat_domisili');
            }

            if (!Schema::hasColumn('data_riwayat_diri', 'kabupaten_id')) {
                $table->string('kabupaten_id', 50)->nullable()->after('provinsi_id');
            }

            if (!Schema::hasColumn('data_riwayat_diri', 'kecamatan_id')) {
                $table->string('kecamatan_id', 50)->nullable()->after('kabupaten_id');
            }

            if (!Schema::hasColumn('data_riwayat_diri', 'kelurahan_id')) {
                $table->string('kelurahan_id', 50)->nullable()->after('kecamatan_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('data_riwayat_diri', function (Blueprint $table) {
            if (Schema::hasColumn('data_riwayat_diri', 'kelurahan_id')) {
                $table->dropColumn('kelurahan_id');
            }

            if (Schema::hasColumn('data_riwayat_diri', 'kecamatan_id')) {
                $table->dropColumn('kecamatan_id');
            }

            if (Schema::hasColumn('data_riwayat_diri', 'kabupaten_id')) {
                $table->dropColumn('kabupaten_id');
            }

            if (Schema::hasColumn('data_riwayat_diri', 'provinsi_id')) {
                $table->dropColumn('provinsi_id');
            }
        });
    }
};