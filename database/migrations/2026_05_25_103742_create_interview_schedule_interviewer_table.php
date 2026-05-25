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
       Schema::create('interview_schedule_interviewer', function (Blueprint $table) {
    $table->id();
    $table->foreignId('schedule_id')
        ->constrained('interview_schedule_header')
        ->cascadeOnDelete();

    $table->unsignedBigInteger('interviewer_id');
    $table->string('role', 100)->nullable();
    $table->boolean('is_lead')->default(false);
    $table->timestamp('created_at')->useCurrent();

    $table->unique(['schedule_id', 'interviewer_id']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interview_schedule_interviewer');
    }
};
