<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('jadwal_interview_kandidat', 'file_cv')) {
            Schema::table('jadwal_interview_kandidat', function (Blueprint $table) {
                $table->string('file_cv')->nullable()->after('hasil_interview');
            });
        }

        if (!Schema::hasColumn('jadwal_interview_kandidat', 'file_foto')) {
            Schema::table('jadwal_interview_kandidat', function (Blueprint $table) {
                $table->string('file_foto')->nullable()->after('file_cv');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('jadwal_interview_kandidat', 'file_foto')) {
            Schema::table('jadwal_interview_kandidat', function (Blueprint $table) {
                $table->dropColumn('file_foto');
            });
        }

        if (Schema::hasColumn('jadwal_interview_kandidat', 'file_cv')) {
            Schema::table('jadwal_interview_kandidat', function (Blueprint $table) {
                $table->dropColumn('file_cv');
            });
        }
    }
};