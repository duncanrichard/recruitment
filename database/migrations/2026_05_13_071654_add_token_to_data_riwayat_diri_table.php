<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_riwayat_diri', function (Blueprint $table) {
            $table->string('token', 100)
                ->nullable()
                ->unique()
                ->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('data_riwayat_diri', function (Blueprint $table) {
            $table->dropUnique(['token']);
            $table->dropColumn('token');
        });
    }
};