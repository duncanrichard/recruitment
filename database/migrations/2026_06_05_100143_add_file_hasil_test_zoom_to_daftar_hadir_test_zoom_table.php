<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('daftar_hadir_test_zoom', 'file_hasil_test_zoom')) {
            Schema::table('daftar_hadir_test_zoom', function (Blueprint $table) {
                $table->string('file_hasil_test_zoom')->nullable()->after('hasil_test');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('daftar_hadir_test_zoom', 'file_hasil_test_zoom')) {
            Schema::table('daftar_hadir_test_zoom', function (Blueprint $table) {
                $table->dropColumn('file_hasil_test_zoom');
            });
        }
    }
};