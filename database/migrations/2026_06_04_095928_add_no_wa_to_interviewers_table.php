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
        Schema::table('interviewers', function (Blueprint $table) {
            if (!Schema::hasColumn('interviewers', 'no_wa')) {
                $table->string('no_wa', 50)->nullable()->after('nama');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interviewers', function (Blueprint $table) {
            if (Schema::hasColumn('interviewers', 'no_wa')) {
                $table->dropColumn('no_wa');
            }
        });
    }
};
