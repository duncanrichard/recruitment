<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offering_letter', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('data_riwayat_diri_id')
                ->constrained('data_riwayat_diri')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

           $table->foreignUuid('pengajuan_dirut_id')
    ->constrained('status_pengajuan_dirut')
    ->cascadeOnUpdate()
    ->restrictOnDelete();

            $table->dateTime('jadwal_ol')->nullable();

            $table->enum('status_ol', [
                'Menerima tawaran',
                'Menolak tawaran',
                'Tidak melanjutkan rekrutmen',
            ])->nullable();

            $table->timestamps();

            $table->softDeletes();

          $table->unsignedBigInteger('deleted_by')->nullable();

$table->foreign('deleted_by')
    ->references('id')
    ->on('users')
    ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offering_letter');
    }
};