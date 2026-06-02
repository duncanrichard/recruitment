<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permintaan_kandidat_recruitments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('pt_membutuhkan')->nullable();
            $table->string('divisi_departemen')->nullable();
            $table->string('permintaan_oleh')->nullable();
            $table->date('tanggal_permintaan')->nullable();

            $table->text('deskripsi_permintaan')->nullable();

            $table->string('nama_posisi_jabatan')->nullable();
            $table->unsignedInteger('jumlah_karyawan')->default(1);
            $table->string('lokasi_kerja')->nullable();

            $table->enum('tipe_pekerjaan', [
                'Kontrak',
                'Tetap',
                'Paruh Waktu',
                'Magang',
                'Freelance',
                'Lainnya',
            ])->nullable();

            $table->string('jadwal_kerja')->nullable();
            $table->longText('deskripsi_pekerjaan')->nullable();
            $table->text('gaji_benefit')->nullable();

            $table->string('pendidikan_minimum')->nullable();
            $table->string('usia')->nullable();
            $table->enum('jenis_kelamin', [
                'Laki-laki',
                'Perempuan',
                'Laki-laki / Perempuan',
            ])->nullable();

            $table->string('pengalaman_kerja')->nullable();
            $table->text('keterampilan_teknis')->nullable();
            $table->text('keterampilan_interpersonal')->nullable();
            $table->text('syarat_khusus')->nullable();
            $table->text('keahlian_khusus')->nullable();
            $table->text('sertifikat')->nullable();

            $table->date('tanggal_mulai_diperlukan')->nullable();

            $table->enum('urgent_permintaan', [
                'Rendah',
                'Sedang',
                'Tinggi',
                'Sangat Urgent',
            ])->nullable();

            $table->enum('alasan_permintaan', [
                'Penggantian',
                'Baru Divisi',
                'Penambahan Karyawan',
                'Lainnya',
            ])->nullable();

            $table->text('karakter_pribadi')->nullable();
            $table->text('hasil_test_tertulis')->nullable();
            $table->text('permintaan_khusus')->nullable();
            $table->text('karakter_profesional')->nullable();

            $table->text('proses_seleksi')->nullable();
            $table->text('materi_ppt')->nullable();
            $table->text('informasi_tambahan')->nullable();
            $table->text('penyebaran_iklan')->nullable();

            $table->enum('status_permintaan', [
                'Draft',
                'Diajukan',
                'Diproses',
                'Selesai',
                'Dibatalkan',
            ])->default('Diajukan');

            $table->timestamps();
            $table->softDeletes();

            $table->index('tanggal_permintaan');
            $table->index('tanggal_mulai_diperlukan');
            $table->index('status_permintaan');
            $table->index('urgent_permintaan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permintaan_kandidat_recruitments');
    }
};