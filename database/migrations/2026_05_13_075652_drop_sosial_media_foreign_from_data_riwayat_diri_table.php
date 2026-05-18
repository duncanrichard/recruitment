<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_riwayat_diri', function (Blueprint $table) {
            if (Schema::hasColumn('data_riwayat_diri', 'sosial_media_id')) {
                $table->dropForeign(['sosial_media_id']);
                $table->dropColumn('sosial_media_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('data_riwayat_diri', function (Blueprint $table) {
            if (!Schema::hasColumn('data_riwayat_diri', 'sosial_media_id')) {
                $table->uuid('sosial_media_id')->nullable()->after('no_wa');

                $table->foreign('sosial_media_id')
                    ->references('id')
                    ->on('sosial_media')
                    ->nullOnDelete();
            }
        });
    }
};