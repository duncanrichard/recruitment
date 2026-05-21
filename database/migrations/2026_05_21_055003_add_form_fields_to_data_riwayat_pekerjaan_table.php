<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $tableName = 'data_riwayat_pekerjaan';

    public function up(): void
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            if (!Schema::hasColumn($this->tableName, 'status_pekerjaan')) {
                $table->string('status_pekerjaan', 100)->nullable();
            }

            if (!Schema::hasColumn($this->tableName, 'posisi_pekerjaan')) {
                $table->string('posisi_pekerjaan')->nullable();
            }

            if (!Schema::hasColumn($this->tableName, 'bidang_pekerjaan')) {
                $table->string('bidang_pekerjaan')->nullable();
            }

            if (!Schema::hasColumn($this->tableName, 'lokasi_perusahaan')) {
                $table->string('lokasi_perusahaan')->nullable();
            }

            if (!Schema::hasColumn($this->tableName, 'tahun_mulai_bekerja')) {
                $table->string('tahun_mulai_bekerja', 50)->nullable();
            }

            if (!Schema::hasColumn($this->tableName, 'tahun_selesai_bekerja')) {
                $table->string('tahun_selesai_bekerja', 50)->nullable();
            }

            if (!Schema::hasColumn($this->tableName, 'lama_bekerja')) {
                $table->string('lama_bekerja', 100)->nullable();
            }

            if (!Schema::hasColumn($this->tableName, 'deskripsi_pekerjaan')) {
                $table->text('deskripsi_pekerjaan')->nullable();
            }

            if (!Schema::hasColumn($this->tableName, 'alasan_berhenti')) {
                $table->text('alasan_berhenti')->nullable();
            }

            if (!Schema::hasColumn($this->tableName, 'keahlian')) {
                $table->text('keahlian')->nullable();
            }

            if (!Schema::hasColumn($this->tableName, 'catatan_pekerjaan')) {
                $table->text('catatan_pekerjaan')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            foreach ([
                'status_pekerjaan',
                'posisi_pekerjaan',
                'bidang_pekerjaan',
                'lokasi_perusahaan',
                'tahun_mulai_bekerja',
                'tahun_selesai_bekerja',
                'lama_bekerja',
                'deskripsi_pekerjaan',
                'alasan_berhenti',
                'keahlian',
                'catatan_pekerjaan',
            ] as $column) {
                if (Schema::hasColumn($this->tableName, $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
