<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_riwayat_diri', function (Blueprint $table) {
            if (!Schema::hasColumn('data_riwayat_diri', 'tanggal_skrining')) {
                $table->date('tanggal_skrining')
                    ->nullable()
                    ->after('tanggal_lahir');
            }
        });
    }

    public function down(): void
    {
        Schema::table('data_riwayat_diri', function (Blueprint $table) {
            if (Schema::hasColumn('data_riwayat_diri', 'tanggal_skrining')) {
                $table->dropColumn('tanggal_skrining');
            }
        });
    }
};