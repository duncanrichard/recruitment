<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_kesiapan_bekerja', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('data_riwayat_diri_id')
                ->constrained('data_riwayat_diri')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->date('kapan_siap_bekerja')->nullable();

            $table->decimal('ekpetasi_gaji', 15, 2)->nullable();

            $table->enum('penempatan', [
                'Jawa Tengah',
                'Jawa Timur',
                'Jawa Barat',
                'Tidak Bersedia',
            ])->nullable();

            $table->enum('proses_bchaking', [
                'Ya',
                'Tidak',
            ])->nullable();

            $table->enum('dapat_dipertanggung_jawabkan', [
                'Ya',
                'Tidak',
            ])->nullable();

            $table->enum('bersedia_training', [
                'Ya',
                'Tidak',
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
        Schema::dropIfExists('data_kesiapan_bekerja');
    }
};