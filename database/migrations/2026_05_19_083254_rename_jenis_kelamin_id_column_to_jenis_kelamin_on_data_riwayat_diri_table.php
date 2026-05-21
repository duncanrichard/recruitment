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
        if (
            Schema::hasColumn('data_riwayat_diri', 'jenis_kelamin_id') &&
            !Schema::hasColumn('data_riwayat_diri', 'jenis_kelamin')
        ) {
            Schema::table('data_riwayat_diri', function (Blueprint $table) {
                $table->renameColumn('jenis_kelamin_id', 'jenis_kelamin');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (
            Schema::hasColumn('data_riwayat_diri', 'jenis_kelamin') &&
            !Schema::hasColumn('data_riwayat_diri', 'jenis_kelamin_id')
        ) {
            Schema::table('data_riwayat_diri', function (Blueprint $table) {
                $table->renameColumn('jenis_kelamin', 'jenis_kelamin_id');
            });
        }
    }
};