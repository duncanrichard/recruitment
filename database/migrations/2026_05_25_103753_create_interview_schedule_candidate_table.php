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
        Schema::create('interview_schedule_candidate', function (Blueprint $table) {
    $table->id();
    $table->foreignId('schedule_id')
        ->constrained('interview_schedule_header')
        ->cascadeOnDelete();

    $table->unsignedBigInteger('data_riwayat_diri_id');

    $table->enum('kehadiran', ['belum_hadir', 'hadir', 'tidak_hadir'])
        ->default('belum_hadir');

    $table->enum('hasil_interview', [
        'belum_dinilai',
        'lolos',
        'tidak_lolos',
        'dipertimbangkan'
    ])->default('belum_dinilai');

    $table->text('notes')->nullable();
    $table->timestamps();

    $table->unique(['schedule_id', 'data_riwayat_diri_id']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interview_schedule_candidate');
    }
};
