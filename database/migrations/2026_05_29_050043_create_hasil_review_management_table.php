<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS "pgcrypto"');

        Schema::create('hasil_review_management', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('hasil_interview_id');
            $table->text('review_management')->nullable();
            $table->string('status', 50)->nullable();
            $table->timestamps();

            $table->foreign('hasil_interview_id')
                ->references('id')
                ->on('jadwal_interview_kandidat')
                ->cascadeOnDelete();

            $table->unique('hasil_interview_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_review_management');
    }
};