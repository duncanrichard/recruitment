<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_interview_kandidat', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('jadwal_interview_id');
            $table->uuid('data_riwayat_diri_id');

            $table->string('status_kehadiran', 100)->nullable();
            $table->string('hasil_interview')->nullable();
            $table->text('catatan')->nullable();

            $table->timestamps();

            $table->foreign('jadwal_interview_id')
                ->references('id')
                ->on('jadwal_interview')
                ->cascadeOnDelete();

            $table->foreign('data_riwayat_diri_id')
                ->references('id')
                ->on('data_riwayat_diri')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_interview_kandidat');
    }
};