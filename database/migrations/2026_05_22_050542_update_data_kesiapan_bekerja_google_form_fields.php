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

        DB::statement("
            ALTER TABLE data_kesiapan_bekerja
            DROP CONSTRAINT IF EXISTS data_kesiapan_bekerja_penempatan_check
        ");

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

        if (Schema::hasColumn('data_kesiapan_bekerja', 'tanggal_siap_kerja')) {
            DB::statement("
                ALTER TABLE data_kesiapan_bekerja
                ALTER COLUMN tanggal_siap_kerja TYPE varchar(255)
                USING tanggal_siap_kerja::varchar
            ");
        }

        Schema::table('data_kesiapan_bekerja', function (Blueprint $table) {
            if (!Schema::hasColumn('data_kesiapan_bekerja', 'kapan_siap_bekerja')) {
                $table->string('kapan_siap_bekerja', 255)->nullable();
            }

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'expetasi_gaji')) {
                $table->decimal('expetasi_gaji', 20, 2)->nullable();
            }

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'penempatan')) {
                $table->text('penempatan')->nullable();
            }

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'penempatan_luar_jawa_tengah')) {
                $table->json('penempatan_luar_jawa_tengah')->nullable();
            }

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'proses_bkhang')) {
                $table->string('proses_bkhang', 255)->nullable();
            }

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'background_checking')) {
                $table->string('background_checking', 255)->nullable();
            }

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'dapat_dipertanggung_jawabkan')) {
                $table->string('dapat_dipertanggung_jawabkan', 255)->nullable();
            }

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'pernyataan_data_benar')) {
                $table->string('pernyataan_data_benar', 255)->nullable();
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

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'bersedia_ditempatkan')) {
                $table->string('bersedia_ditempatkan', 255)->nullable();
            }

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'bersedia_shift')) {
                $table->string('bersedia_shift', 255)->nullable();
            }

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'bersedia_lembur')) {
                $table->string('bersedia_lembur', 255)->nullable();
            }

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'bersedia_hari_libur')) {
                $table->string('bersedia_hari_libur', 255)->nullable();
            }

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'memiliki_kendaraan')) {
                $table->string('memiliki_kendaraan', 255)->nullable();
            }

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'memiliki_sim')) {
                $table->string('memiliki_sim', 255)->nullable();
            }

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'bersedia_pelatihan')) {
                $table->string('bersedia_pelatihan', 255)->nullable();
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

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'bersedia_training')) {
                $table->string('bersedia_training', 255)->nullable();
            }
        });

        if (
            Schema::hasTable('posisi') &&
            Schema::hasColumn('data_kesiapan_bekerja', 'posisi_dilamar_id')
        ) {
            DB::statement("
                DO $$
                BEGIN
                    IF NOT EXISTS (
                        SELECT 1
                        FROM pg_constraint
                        WHERE conname = 'data_kesiapan_bekerja_posisi_dilamar_id_foreign'
                    ) THEN
                        ALTER TABLE data_kesiapan_bekerja
                        ADD CONSTRAINT data_kesiapan_bekerja_posisi_dilamar_id_foreign
                        FOREIGN KEY (posisi_dilamar_id)
                        REFERENCES posisi(id)
                        ON DELETE SET NULL;
                    END IF;
                END
                $$;
            ");
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('data_kesiapan_bekerja')) {
            return;
        }

        DB::statement("
            ALTER TABLE data_kesiapan_bekerja
            DROP CONSTRAINT IF EXISTS data_kesiapan_bekerja_posisi_dilamar_id_foreign
        ");

        Schema::table('data_kesiapan_bekerja', function (Blueprint $table) {
            $columns = [
                'penempatan_luar_jawa_tengah',
                'background_checking',
                'pernyataan_data_benar',
                'tanggal_siap_kerja',
                'gaji_diharapkan',
                'posisi_dilamar_id',
                'lokasi_kerja_diinginkan',
                'bersedia_ditempatkan',
                'bersedia_shift',
                'bersedia_lembur',
                'bersedia_hari_libur',
                'memiliki_kendaraan',
                'memiliki_sim',
                'bersedia_pelatihan',
                'status_ikatan_kerja',
                'alasan_melamar',
                'catatan_kesiapan',
                'bersedia_training',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('data_kesiapan_bekerja', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};