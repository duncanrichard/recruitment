<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_riwayat_diri', function (Blueprint $table) {
            $table->string('sumber_informasi')->nullable()->after('sosial_media_id');
        });
    }

    public function down(): void
    {
        Schema::table('data_riwayat_diri', function (Blueprint $table) {
            $table->dropColumn('sumber_informasi');
        });
    }
};