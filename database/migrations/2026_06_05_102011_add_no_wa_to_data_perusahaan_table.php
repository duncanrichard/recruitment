<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('data_perusahaan', 'no_wa')) {
            Schema::table('data_perusahaan', function (Blueprint $table) {
                $table->string('no_wa', 30)->nullable()->after('nama_perusahaan');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('data_perusahaan', 'no_wa')) {
            Schema::table('data_perusahaan', function (Blueprint $table) {
                $table->dropColumn('no_wa');
            });
        }
    }
};