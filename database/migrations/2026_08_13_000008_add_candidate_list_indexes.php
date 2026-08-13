<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_riwayat_diri', function (Blueprint $table) {
            $table->index(['perusahaan_dilamar', 'created_at'], 'pelamar_company_created_idx');
            $table->index(['perusahaan_dilamar', 'tanggal_skrining'], 'pelamar_company_screening_idx');
            $table->index('created_at', 'pelamar_created_at_idx');
        });
    }

    public function down(): void
    {
        Schema::table('data_riwayat_diri', function (Blueprint $table) {
            $table->dropIndex('pelamar_company_created_idx');
            $table->dropIndex('pelamar_company_screening_idx');
            $table->dropIndex('pelamar_created_at_idx');
        });
    }
};
