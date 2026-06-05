<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('offering_letter');
    }

    public function down(): void
    {
        Schema::create('offering_letter', function ($table) {
            $table->uuid('id')->primary();
            $table->uuid('data_riwayat_diri_id');
            $table->uuid('pengajuan_dirut_id');
            $table->timestamp('jadwal_ol')->nullable();
            $table->string('status_ol', 25)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->uuid('deleted_by')->nullable();

            $table->foreign('data_riwayat_diri_id')
                ->references('id')
                ->on('data_riwayat_diri')
                ->cascadeOnDelete();
        });
    }
};