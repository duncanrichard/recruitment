<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * Karena database kamu PostgreSQL dan data_riwayat_diri.id bertipe UUID,
         * maka interview_schedule_candidate.data_riwayat_diri_id juga harus UUID.
         *
         * Jika tabel masih kosong, cara paling aman adalah drop column lalu buat ulang.
         */

        DB::statement("
            ALTER TABLE interview_schedule_candidate
            DROP CONSTRAINT IF EXISTS interview_schedule_candidate_schedule_id_data_riwayat_diri_id_unique
        ");

        DB::statement("
            ALTER TABLE interview_schedule_candidate
            DROP CONSTRAINT IF EXISTS fk_interview_candidate_schedule
        ");

        DB::statement("
            ALTER TABLE interview_schedule_candidate
            DROP CONSTRAINT IF EXISTS fk_interview_candidate_data_riwayat_diri
        ");

        Schema::table('interview_schedule_candidate', function (Blueprint $table) {
            $table->dropColumn('data_riwayat_diri_id');
        });

        Schema::table('interview_schedule_candidate', function (Blueprint $table) {
            $table->uuid('data_riwayat_diri_id')->after('schedule_id');

            $table->foreign('schedule_id', 'fk_interview_candidate_schedule')
                ->references('id')
                ->on('interview_schedule_header')
                ->onDelete('cascade');

            $table->foreign('data_riwayat_diri_id', 'fk_interview_candidate_data_riwayat_diri')
                ->references('id')
                ->on('data_riwayat_diri')
                ->onDelete('cascade');

            $table->unique(
                ['schedule_id', 'data_riwayat_diri_id'],
                'interview_candidate_schedule_pelamar_unique'
            );
        });
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE interview_schedule_candidate
            DROP CONSTRAINT IF EXISTS interview_candidate_schedule_pelamar_unique
        ");

        DB::statement("
            ALTER TABLE interview_schedule_candidate
            DROP CONSTRAINT IF EXISTS fk_interview_candidate_schedule
        ");

        DB::statement("
            ALTER TABLE interview_schedule_candidate
            DROP CONSTRAINT IF EXISTS fk_interview_candidate_data_riwayat_diri
        ");

        Schema::table('interview_schedule_candidate', function (Blueprint $table) {
            $table->dropColumn('data_riwayat_diri_id');
        });

        Schema::table('interview_schedule_candidate', function (Blueprint $table) {
            $table->unsignedBigInteger('data_riwayat_diri_id')->after('schedule_id');
        });
    }
};