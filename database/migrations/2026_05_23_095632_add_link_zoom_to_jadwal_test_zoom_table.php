<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_test_zoom', function (Blueprint $table) {
            if (!Schema::hasColumn('jadwal_test_zoom', 'link_zoom')) {
                $table->string('link_zoom', 2048)
                    ->nullable()
                    ->after('jadwal');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_test_zoom', function (Blueprint $table) {
            if (Schema::hasColumn('jadwal_test_zoom', 'link_zoom')) {
                $table->dropColumn('link_zoom');
            }
        });
    }
};