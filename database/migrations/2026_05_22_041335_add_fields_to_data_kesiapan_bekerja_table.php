<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('data_kesiapan_bekerja')) {
            return;
        }

        Schema::table('data_kesiapan_bekerja', function (Blueprint $table) {
            if (!Schema::hasColumn('data_kesiapan_bekerja', 'bersedia_ditempatkan')) {
                $table->string('bersedia_ditempatkan', 50)->nullable()->after('data_riwayat_diri_id');
            }

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'bersedia_shift')) {
                $table->string('bersedia_shift', 50)->nullable()->after('bersedia_ditempatkan');
            }

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'bersedia_lembur')) {
                $table->string('bersedia_lembur', 50)->nullable()->after('bersedia_shift');
            }

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'bersedia_hari_libur')) {
                $table->string('bersedia_hari_libur', 50)->nullable()->after('bersedia_lembur');
            }

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'tanggal_siap_kerja')) {
                $table->date('tanggal_siap_kerja')->nullable()->after('bersedia_hari_libur');
            }

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'gaji_diharapkan')) {
                $table->decimal('gaji_diharapkan', 20, 2)->nullable()->after('tanggal_siap_kerja');
            }

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'posisi_dilamar_id')) {
                $table->uuid('posisi_dilamar_id')->nullable()->after('gaji_diharapkan');
            }

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'lokasi_kerja_diinginkan')) {
                $table->string('lokasi_kerja_diinginkan', 255)->nullable()->after('posisi_dilamar_id');
            }

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'memiliki_kendaraan')) {
                $table->string('memiliki_kendaraan', 50)->nullable()->after('lokasi_kerja_diinginkan');
            }

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'memiliki_sim')) {
                $table->string('memiliki_sim', 100)->nullable()->after('memiliki_kendaraan');
            }

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'bersedia_pelatihan')) {
                $table->string('bersedia_pelatihan', 50)->nullable()->after('memiliki_sim');
            }

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'status_ikatan_kerja')) {
                $table->string('status_ikatan_kerja', 255)->nullable()->after('bersedia_pelatihan');
            }

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'alasan_melamar')) {
                $table->text('alasan_melamar')->nullable()->after('status_ikatan_kerja');
            }

            if (!Schema::hasColumn('data_kesiapan_bekerja', 'catatan_kesiapan')) {
                $table->text('catatan_kesiapan')->nullable()->after('alasan_melamar');
            }
        });

        /*
        |--------------------------------------------------------------------------
        | Sinkronisasi dari kolom lama ke kolom baru
        |--------------------------------------------------------------------------
        | Kolom lama yang terlihat di tabel:
        | - kapan_siap_bekerja
        | - expetasi_gaji
        | - penempatan
        | - bersedia_training
        */

        if (
            Schema::hasColumn('data_kesiapan_bekerja', 'tanggal_siap_kerja') &&
            Schema::hasColumn('data_kesiapan_bekerja', 'kapan_siap_bekerja')
        ) {
            DB::table('data_kesiapan_bekerja')
                ->whereNull('tanggal_siap_kerja')
                ->whereNotNull('kapan_siap_bekerja')
                ->update([
                    'tanggal_siap_kerja' => DB::raw('kapan_siap_bekerja'),
                ]);
        }

        if (
            Schema::hasColumn('data_kesiapan_bekerja', 'gaji_diharapkan') &&
            Schema::hasColumn('data_kesiapan_bekerja', 'expetasi_gaji')
        ) {
            DB::table('data_kesiapan_bekerja')
                ->whereNull('gaji_diharapkan')
                ->whereNotNull('expetasi_gaji')
                ->update([
                    'gaji_diharapkan' => DB::raw('expetasi_gaji'),
                ]);
        }

        if (
            Schema::hasColumn('data_kesiapan_bekerja', 'lokasi_kerja_diinginkan') &&
            Schema::hasColumn('data_kesiapan_bekerja', 'penempatan')
        ) {
            DB::table('data_kesiapan_bekerja')
                ->whereNull('lokasi_kerja_diinginkan')
                ->whereNotNull('penempatan')
                ->update([
                    'lokasi_kerja_diinginkan' => DB::raw('penempatan'),
                ]);
        }

        if (
            Schema::hasColumn('data_kesiapan_bekerja', 'bersedia_pelatihan') &&
            Schema::hasColumn('data_kesiapan_bekerja', 'bersedia_training')
        ) {
            DB::table('data_kesiapan_bekerja')
                ->whereNull('bersedia_pelatihan')
                ->whereNotNull('bersedia_training')
                ->update([
                    'bersedia_pelatihan' => DB::raw('bersedia_training'),
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Ambil posisi dari data_riwayat_diri kalau tersedia
        |--------------------------------------------------------------------------
        */

        if (
            Schema::hasColumn('data_kesiapan_bekerja', 'posisi_dilamar_id') &&
            Schema::hasTable('data_riwayat_diri') &&
            Schema::hasTable('posisi') &&
            Schema::hasColumn('data_riwayat_diri', 'posisi_dilamar')
        ) {
            DB::statement("
                UPDATE data_kesiapan_bekerja AS kesiapan
                SET posisi_dilamar_id = diri.posisi_dilamar
                FROM data_riwayat_diri AS diri
                WHERE kesiapan.data_riwayat_diri_id = diri.id
                AND kesiapan.posisi_dilamar_id IS NULL
                AND diri.posisi_dilamar IS NOT NULL
                AND EXISTS (
                    SELECT 1
                    FROM posisi
                    WHERE posisi.id = diri.posisi_dilamar
                )
            ");
        }

        /*
        |--------------------------------------------------------------------------
        | Foreign Key ke tabel posisi
        |--------------------------------------------------------------------------
        */

        if (
            Schema::hasTable('posisi') &&
            Schema::hasColumn('data_kesiapan_bekerja', 'posisi_dilamar_id')
        ) {
            Schema::table('data_kesiapan_bekerja', function (Blueprint $table) {
                $table->foreign('posisi_dilamar_id')
                    ->references('id')
                    ->on('posisi')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('data_kesiapan_bekerja')) {
            return;
        }

        Schema::table('data_kesiapan_bekerja', function (Blueprint $table) {
            if (Schema::hasColumn('data_kesiapan_bekerja', 'posisi_dilamar_id')) {
                $table->dropForeign(['posisi_dilamar_id']);
            }
        });

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
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('data_kesiapan_bekerja', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};