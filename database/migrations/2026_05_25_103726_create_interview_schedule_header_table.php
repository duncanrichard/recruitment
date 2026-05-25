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
       Schema::create('interview_schedule_header', function (Blueprint $table) {
    $table->id();
    $table->string('schedule_code', 50)->unique();
    $table->string('title', 150);
    $table->date('interview_date');
    $table->time('start_time');
    $table->time('end_time');
    $table->string('location')->nullable();
    $table->enum('interview_type', ['offline', 'online'])->default('offline');
    $table->string('meeting_link')->nullable();
    $table->enum('status', ['draft', 'scheduled', 'completed', 'cancelled'])->default('draft');
    $table->text('notes')->nullable();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interview_schedule_header');
    }
};
