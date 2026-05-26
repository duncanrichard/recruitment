<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_interview_panelis', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('jadwal_interview_id');
            $table->uuid('interviewer_id');

            $table->string('role', 100)->nullable();
            $table->boolean('is_lead')->default(false);

            $table->timestamp('created_at')->nullable();

            $table->foreign('jadwal_interview_id')
                ->references('id')
                ->on('jadwal_interview')
                ->cascadeOnDelete();

            $table->foreign('interviewer_id')
                ->references('id')
                ->on('interviewers')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_interview_panelis');
    }
};