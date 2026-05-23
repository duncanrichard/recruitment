<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_test_zoom', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('data_riwayat_diri_id');
            $table->dateTime('jadwal')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('data_riwayat_diri_id')
                ->references('id')
                ->on('data_riwayat_diri')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_test_zoom');
    }
};