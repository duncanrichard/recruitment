<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_riwayat_diri', function (Blueprint $table) {
            if (!Schema::hasColumn('data_riwayat_diri', 'sumber_informasi_id')) {
                $table->uuid('sumber_informasi_id')->nullable()->after('perusahaan_dilamar');

                $table->foreign('sumber_informasi_id')
                    ->references('id')
                    ->on('sumber_informasi')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('data_riwayat_diri', function (Blueprint $table) {
            if (Schema::hasColumn('data_riwayat_diri', 'sumber_informasi_id')) {
                $table->dropForeign(['sumber_informasi_id']);
                $table->dropColumn('sumber_informasi_id');
            }
        });
    }
};