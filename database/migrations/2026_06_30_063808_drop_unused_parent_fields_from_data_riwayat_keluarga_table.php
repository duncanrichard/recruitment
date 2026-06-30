<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_riwayat_keluarga', function (Blueprint $table) {
            $columns = [
                'nama_ayah_kandung',
                'pekerjaan_ayah_kandung',
                'nama_ibu_kandung',
                'pekerjaan_ibu_kandung',

                'nik_ayah',
                'tempat_lahir_ayah',
                'tanggal_lahir_ayah',
                'pekerjaan_ayah',

                'nik_ibu',
                'tempat_lahir_ibu',
                'tanggal_lahir_ibu',
                'pekerjaan_ibu',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('data_riwayat_keluarga', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('data_riwayat_keluarga', function (Blueprint $table) {
            if (!Schema::hasColumn('data_riwayat_keluarga', 'nama_ayah_kandung')) {
                $table->string('nama_ayah_kandung')->nullable();
            }

            if (!Schema::hasColumn('data_riwayat_keluarga', 'pekerjaan_ayah_kandung')) {
                $table->string('pekerjaan_ayah_kandung')->nullable();
            }

            if (!Schema::hasColumn('data_riwayat_keluarga', 'nama_ibu_kandung')) {
                $table->string('nama_ibu_kandung')->nullable();
            }

            if (!Schema::hasColumn('data_riwayat_keluarga', 'pekerjaan_ibu_kandung')) {
                $table->string('pekerjaan_ibu_kandung')->nullable();
            }

            if (!Schema::hasColumn('data_riwayat_keluarga', 'nik_ayah')) {
                $table->string('nik_ayah', 50)->nullable();
            }

            if (!Schema::hasColumn('data_riwayat_keluarga', 'tempat_lahir_ayah')) {
                $table->string('tempat_lahir_ayah')->nullable();
            }

            if (!Schema::hasColumn('data_riwayat_keluarga', 'tanggal_lahir_ayah')) {
                $table->date('tanggal_lahir_ayah')->nullable();
            }

            if (!Schema::hasColumn('data_riwayat_keluarga', 'pekerjaan_ayah')) {
                $table->string('pekerjaan_ayah')->nullable();
            }

            if (!Schema::hasColumn('data_riwayat_keluarga', 'nik_ibu')) {
                $table->string('nik_ibu', 50)->nullable();
            }

            if (!Schema::hasColumn('data_riwayat_keluarga', 'tempat_lahir_ibu')) {
                $table->string('tempat_lahir_ibu')->nullable();
            }

            if (!Schema::hasColumn('data_riwayat_keluarga', 'tanggal_lahir_ibu')) {
                $table->date('tanggal_lahir_ibu')->nullable();
            }

            if (!Schema::hasColumn('data_riwayat_keluarga', 'pekerjaan_ibu')) {
                $table->string('pekerjaan_ibu')->nullable();
            }
        });
    }
};