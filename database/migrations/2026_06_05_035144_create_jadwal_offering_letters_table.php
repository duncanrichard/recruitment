<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_offering_letters', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('hasil_review_management_id')->unique();

            $table->date('tanggal_ol');
            $table->time('jam_ol');

            $table->string('metode')->nullable(); 
            $table->string('lokasi')->nullable();
            $table->string('link')->nullable();
            $table->string('pic')->nullable();

            $table->text('catatan')->nullable();

            $table->string('status_jadwal', 50)->default('Terjadwal');

            $table->timestamps();

            $table->foreign('hasil_review_management_id')
                ->references('id')
                ->on('hasil_review_management')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_offering_letters');
    }
};