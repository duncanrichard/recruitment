<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('sosial_media');

        Schema::create('sosial_media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('data_riwayat_diri_id')->nullable();

            $table->string('platform', 100)->nullable();
            $table->string('nama_account')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('data_riwayat_diri_id')
                ->references('id')
                ->on('data_riwayat_diri')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sosial_media');
    }
};
