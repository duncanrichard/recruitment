<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_interview_panelis', function (Blueprint $table) {
            if (Schema::hasColumn('jadwal_interview_panelis', 'role')) {
                $table->dropColumn('role');
            }

            if (Schema::hasColumn('jadwal_interview_panelis', 'is_lead')) {
                $table->dropColumn('is_lead');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_interview_panelis', function (Blueprint $table) {
            if (!Schema::hasColumn('jadwal_interview_panelis', 'role')) {
                $table->string('role', 100)->nullable();
            }

            if (!Schema::hasColumn('jadwal_interview_panelis', 'is_lead')) {
                $table->boolean('is_lead')->default(false);
            }
        });
    }
};