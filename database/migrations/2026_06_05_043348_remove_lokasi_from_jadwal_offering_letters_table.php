<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_offering_letters', function (Blueprint $table) {
            if (Schema::hasColumn('jadwal_offering_letters', 'lokasi')) {
                $table->dropColumn('lokasi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_offering_letters', function (Blueprint $table) {
            if (!Schema::hasColumn('jadwal_offering_letters', 'lokasi')) {
                $table->string('lokasi')->nullable()->after('metode');
            }
        });
    }
};