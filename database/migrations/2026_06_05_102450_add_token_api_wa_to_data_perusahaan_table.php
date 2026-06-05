<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('data_perusahaan', 'token_api_wa')) {
            Schema::table('data_perusahaan', function (Blueprint $table) {
                $table->text('token_api_wa')->nullable()->after('no_wa');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('data_perusahaan', 'token_api_wa')) {
            Schema::table('data_perusahaan', function (Blueprint $table) {
                $table->dropColumn('token_api_wa');
            });
        }
    }
};