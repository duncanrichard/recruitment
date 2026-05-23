<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        if (!Schema::hasTable('data_kesiapan_bekerja')) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Drop foreign key jika ada
        |--------------------------------------------------------------------------
        */
        DB::statement("
            ALTER TABLE data_kesiapan_bekerja
            DROP CONSTRAINT IF EXISTS data_kesiapan_bekerja_posisi_dilamar_id_foreign
        ");

        /*
        |--------------------------------------------------------------------------
        | Drop field yang tidak dipakai di Google Form
        |--------------------------------------------------------------------------
        | Field yang dipertahankan:
        | - id
        | - data_riwayat_diri_id
        | - kapan_siap_bekerja
        | - expetasi_gaji
        | - penempatan
        | - proses_bkhang
        | - dapat_dipertanggung_jawabkan
        | - bersedia_training
        | - created_at
        | - updated_at
        | - deleted_at
        | - deleted_by
        */

        Schema::table('data_kesiapan_bekerja', function (Blueprint $table) {
            $columns = [
                'bersedia_ditempatkan',
                'bersedia_shift',
                'bersedia_lembur',
                'bersedia_hari_libur',
                'tanggal_siap_kerja',
                'gaji_diharapkan',
                'posisi_dilamar_id',
                'lokasi_kerja_diinginkan',
                'memiliki_kendaraan',
                'memiliki_sim',
                'bersedia_pelatihan',
                'status_ikatan_kerja',
                'alasan_melamar',
                'catatan_kesiapan',
                'penempatan_luar_jawa_tengah',
                'background_checking',
                'pernyataan_data_benar',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('data_kesiapan_bekerja', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        /*
        |--------------------------------------------------------------------------
        | Pastikan tipe kolom yang dipakai sesuai Google Form
        |--------------------------------------------------------------------------
        */

        if (Schema::hasColumn('data_kesiapan_bekerja', 'kapan_siap_bekerja')) {
            DB::statement("
                ALTER TABLE data_kesiapan_bekerja
                ALTER COLUMN kapan_siap_bekerja TYPE varchar(255)
                USING kapan_siap_bekerja::varchar
            ");
        }

        if (Schema::hasColumn('data_kesiapan_bekerja', 'penempatan')) {
            DB::statement("
                ALTER TABLE data_kesiapan_bekerja
                ALTER COLUMN penempatan TYPE text
                USING penempatan::text
            ");
        }

        /*
        |--------------------------------------------------------------------------
        | Hapus check constraint lama penempatan
        |--------------------------------------------------------------------------
        */
        DB::statement("
            ALTER TABLE data_kesiapan_bekerja
            DROP CONSTRAINT IF EXISTS data_kesiapan_bekerja_penempatan_check
        ");
    }

    public function down(): void
    {
        if (!Schema::hasTable('data_kesiapan_bekerja')) {
            return;
        }

        Schema::table('data_kesiapan_bekerja', function (Blueprint $table) {
            if (!Schema::hasColumn('data_kesiapan_bekerja', 'bersedia_ditempatkan')) {
                $table->string('bersedia_ditempatkan', 50)->nullable();
            }

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'bersedia_shift')) {
                $table->string('bersedia_shift', 50)->nullable();
            }

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'bersedia_lembur')) {
                $table->string('bersedia_lembur', 50)->nullable();
            }

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'bersedia_hari_libur')) {
                $table->string('bersedia_hari_libur', 50)->nullable();
            }

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'tanggal_siap_kerja')) {
                $table->string('tanggal_siap_kerja', 255)->nullable();
            }

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'gaji_diharapkan')) {
                $table->decimal('gaji_diharapkan', 20, 2)->nullable();
            }

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'posisi_dilamar_id')) {
                $table->uuid('posisi_dilamar_id')->nullable();
            }

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'lokasi_kerja_diinginkan')) {
                $table->string('lokasi_kerja_diinginkan', 255)->nullable();
            }

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'memiliki_kendaraan')) {
                $table->string('memiliki_kendaraan', 50)->nullable();
            }

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'memiliki_sim')) {
                $table->string('memiliki_sim', 100)->nullable();
            }

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'bersedia_pelatihan')) {
                $table->string('bersedia_pelatihan', 50)->nullable();
            }

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'status_ikatan_kerja')) {
                $table->string('status_ikatan_kerja', 255)->nullable();
            }

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'alasan_melamar')) {
                $table->text('alasan_melamar')->nullable();
            }

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'catatan_kesiapan')) {
                $table->text('catatan_kesiapan')->nullable();
            }

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'penempatan_luar_jawa_tengah')) {
                $table->json('penempatan_luar_jawa_tengah')->nullable();
            }

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'background_checking')) {
                $table->string('background_checking', 255)->nullable();
            }

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'pernyataan_data_benar')) {
                $table->string('pernyataan_data_benar', 255)->nullable();
            }
        });
    }
};