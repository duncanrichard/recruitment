<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_riwayat_diri', function (Blueprint $table) {
            $table->enum('hasil_administrasi', ['lolos', 'gagal'])
                ->nullable()
                ->after('perusahaan_dilamar');
        });
    }

    public function down(): void
    {
        Schema::table('data_riwayat_diri', function (Blueprint $table) {
            $table->dropColumn('hasil_administrasi');
        });
    }
};