<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | data_riwayat_keluarga
        |--------------------------------------------------------------------------
        */
        Schema::table('data_riwayat_keluarga', function (Blueprint $table) {
            if (!Schema::hasColumn('data_riwayat_keluarga', 'nama_ayah')) {
                $table->string('nama_ayah', 255)->nullable();
            }

            if (!Schema::hasColumn('data_riwayat_keluarga', 'nik_ayah')) {
                $table->string('nik_ayah', 50)->nullable();
            }

            if (!Schema::hasColumn('data_riwayat_keluarga', 'tempat_lahir_ayah')) {
                $table->string('tempat_lahir_ayah', 255)->nullable();
            }

            if (!Schema::hasColumn('data_riwayat_keluarga', 'tanggal_lahir_ayah')) {
                $table->date('tanggal_lahir_ayah')->nullable();
            }

            if (!Schema::hasColumn('data_riwayat_keluarga', 'pekerjaan_ayah')) {
                $table->string('pekerjaan_ayah', 255)->nullable();
            }

            if (!Schema::hasColumn('data_riwayat_keluarga', 'no_hp_ayah')) {
                $table->string('no_hp_ayah', 50)->nullable();
            }

            if (!Schema::hasColumn('data_riwayat_keluarga', 'alamat_ayah')) {
                $table->text('alamat_ayah')->nullable();
            }

            if (!Schema::hasColumn('data_riwayat_keluarga', 'nama_ibu')) {
                $table->string('nama_ibu', 255)->nullable();
            }

            if (!Schema::hasColumn('data_riwayat_keluarga', 'nik_ibu')) {
                $table->string('nik_ibu', 50)->nullable();
            }

            if (!Schema::hasColumn('data_riwayat_keluarga', 'tempat_lahir_ibu')) {
                $table->string('tempat_lahir_ibu', 255)->nullable();
            }

            if (!Schema::hasColumn('data_riwayat_keluarga', 'tanggal_lahir_ibu')) {
                $table->date('tanggal_lahir_ibu')->nullable();
            }

            if (!Schema::hasColumn('data_riwayat_keluarga', 'pekerjaan_ibu')) {
                $table->string('pekerjaan_ibu', 255)->nullable();
            }

            if (!Schema::hasColumn('data_riwayat_keluarga', 'no_hp_ibu')) {
                $table->string('no_hp_ibu', 50)->nullable();
            }

            if (!Schema::hasColumn('data_riwayat_keluarga', 'alamat_ibu')) {
                $table->text('alamat_ibu')->nullable();
            }

            /*
            | Field lama dari tabel kamu:
            | kerabat_bekerja_diinstansi = varchar(255)
            |
            | Field baru ini dipakai untuk checkbox multi-select.
            | Isinya array JSON, contoh:
            | ["TNI", "ASN"]
            */
            if (!Schema::hasColumn('data_riwayat_keluarga', 'hubungan_kerabat_instansi')) {
                $table->json('hubungan_kerabat_instansi')->nullable();
            }

            /*
            | Kontak darurat di form berupa array:
            | [{ nama, status, nomor }]
            */
            if (!Schema::hasColumn('data_riwayat_keluarga', 'kontak_darurat')) {
                $table->json('kontak_darurat')->nullable();
            }
        });

        /*
        |--------------------------------------------------------------------------
        | data_saudara_kandung
        |--------------------------------------------------------------------------
        */
        Schema::table('data_saudara_kandung', function (Blueprint $table) {
            if (!Schema::hasColumn('data_saudara_kandung', 'jenis_kelamin')) {
                $table->string('jenis_kelamin', 50)->nullable();
            }

            if (!Schema::hasColumn('data_saudara_kandung', 'hubungan')) {
                $table->string('hubungan', 100)->nullable();
            }

            if (!Schema::hasColumn('data_saudara_kandung', 'no_hp')) {
                $table->string('no_hp', 50)->nullable();
            }

            if (!Schema::hasColumn('data_saudara_kandung', 'alamat')) {
                $table->text('alamat')->nullable();
            }
        });

        /*
        |--------------------------------------------------------------------------
        | data_saudara_ipar
        |--------------------------------------------------------------------------
        */
        Schema::table('data_saudara_ipar', function (Blueprint $table) {
            if (!Schema::hasColumn('data_saudara_ipar', 'jenis_kelamin')) {
                $table->string('jenis_kelamin', 50)->nullable();
            }

            if (!Schema::hasColumn('data_saudara_ipar', 'hubungan')) {
                $table->string('hubungan', 100)->nullable();
            }

            if (!Schema::hasColumn('data_saudara_ipar', 'no_hp')) {
                $table->string('no_hp', 50)->nullable();
            }

            if (!Schema::hasColumn('data_saudara_ipar', 'alamat')) {
                $table->text('alamat')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('data_saudara_ipar', function (Blueprint $table) {
            foreach ([
                'alamat',
                'no_hp',
                'hubungan',
                'jenis_kelamin',
            ] as $column) {
                if (Schema::hasColumn('data_saudara_ipar', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('data_saudara_kandung', function (Blueprint $table) {
            foreach ([
                'alamat',
                'no_hp',
                'hubungan',
                'jenis_kelamin',
            ] as $column) {
                if (Schema::hasColumn('data_saudara_kandung', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('data_riwayat_keluarga', function (Blueprint $table) {
            foreach ([
                'kontak_darurat',
                'hubungan_kerabat_instansi',
                'alamat_ibu',
                'no_hp_ibu',
                'pekerjaan_ibu',
                'tanggal_lahir_ibu',
                'tempat_lahir_ibu',
                'nik_ibu',
                'nama_ibu',
                'alamat_ayah',
                'no_hp_ayah',
                'pekerjaan_ayah',
                'tanggal_lahir_ayah',
                'tempat_lahir_ayah',
                'nik_ayah',
                'nama_ayah',
            ] as $column) {
                if (Schema::hasColumn('data_riwayat_keluarga', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};