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
        Schema::table('data_riwayat_diri', function (Blueprint $table) {
            if (!Schema::hasColumn('data_riwayat_diri', 'tempat_lahir')) {
                $table->string('tempat_lahir')->nullable()->after('nama_institusi');
            }

            if (!Schema::hasColumn('data_riwayat_diri', 'jenis_kelamin_id')) {
                $table->string('jenis_kelamin_id')->nullable()->after('tanggal_lahir');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_riwayat_diri', function (Blueprint $table) {
            if (Schema::hasColumn('data_riwayat_diri', 'jenis_kelamin_id')) {
                $table->dropColumn('jenis_kelamin_id');
            }

            if (Schema::hasColumn('data_riwayat_diri', 'tempat_lahir')) {
                $table->dropColumn('tempat_lahir');
            }
        });
    }
};