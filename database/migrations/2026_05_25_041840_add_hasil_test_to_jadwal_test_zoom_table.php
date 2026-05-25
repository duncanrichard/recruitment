<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_test_zoom', function (Blueprint $table) {
            $table->enum('hasil_test', ['lolos', 'gagal'])
                ->nullable()
                ->after('kehadiran');
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_test_zoom', function (Blueprint $table) {
            $table->dropColumn('hasil_test');
        });
    }
};