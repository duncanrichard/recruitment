<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_riwayat_diri', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('posisi_yang_dilamar')->index(); // relasi ke tabel stores
            $table->string('perusahaan_dilamar')->nullable();

            $table->string('nama_lengkap');
            $table->string('nama_panggil')->nullable();
            $table->string('email')->nullable();

            $table->uuid('pendidikan_id')->nullable()->index();
            $table->string('jurusan')->nullable();
            $table->string('nama_institusi')->nullable();

            $table->uuid('agama_id')->nullable()->index();
            $table->date('tanggal_lahir')->nullable();

            $table->text('alamat_ktp')->nullable();
            $table->text('alamat_domisili')->nullable();

            $table->uuid('kewarganegaraan_id')->nullable()->index();
            $table->uuid('status_pernikahan_id')->nullable()->index();

            $table->string('telepon')->nullable();

            $table->uuid('sosial_media_id')->nullable()->index();
            $table->string('gol_darah')->nullable();
            $table->integer('tinggi_badan')->nullable();
            $table->integer('berat_badan')->nullable();

            $table->enum('str_aktif', ['Y', 'Tidak'])->default('Y');

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();

            $table->uuid('deleted_by')->nullable()->index();

            // Foreign key optional
            // $table->foreign('posisi_yang_dilamar')->references('id')->on('stores')->nullOnDelete();
            // $table->foreign('pendidikan_id')->references('id')->on('pendidikan')->nullOnDelete();
            // $table->foreign('agama_id')->references('id')->on('agama')->nullOnDelete();
            // $table->foreign('kewarganegaraan_id')->references('id')->on('kewarganegaraan')->nullOnDelete();
            // $table->foreign('status_pernikahan_id')->references('id')->on('status_pernikahan')->nullOnDelete();
            // $table->foreign('sosial_media_id')->references('id')->on('sosial_media')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_riwayat_diri');
    }
};