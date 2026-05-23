<?php

use Illuminate\Database\Migrations\Migration;
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

        DB::statement("
            ALTER TABLE data_kesiapan_bekerja
            DROP CONSTRAINT IF EXISTS data_kesiapan_bekerja_proses_bkhang_check
        ");

        DB::statement("
            ALTER TABLE data_kesiapan_bekerja
            DROP CONSTRAINT IF EXISTS data_kesiapan_bekerja_dapat_dipertanggung_jawabkan_check
        ");

        DB::statement("
            ALTER TABLE data_kesiapan_bekerja
            DROP CONSTRAINT IF EXISTS data_kesiapan_bekerja_bersedia_training_check
        ");

        if (Schema::hasColumn('data_kesiapan_bekerja', 'penempatan')) {
            DB::statement("
                ALTER TABLE data_kesiapan_bekerja
                ALTER COLUMN penempatan TYPE text
                USING penempatan::text
            ");
        }

        if (Schema::hasColumn('data_kesiapan_bekerja', 'proses_bkhang')) {
            DB::statement("
                ALTER TABLE data_kesiapan_bekerja
                ALTER COLUMN proses_bkhang TYPE varchar(255)
                USING proses_bkhang::varchar
            ");
        }

        if (Schema::hasColumn('data_kesiapan_bekerja', 'dapat_dipertanggung_jawabkan')) {
            DB::statement("
                ALTER TABLE data_kesiapan_bekerja
                ALTER COLUMN dapat_dipertanggung_jawabkan TYPE varchar(255)
                USING dapat_dipertanggung_jawabkan::varchar
            ");
        }

        if (Schema::hasColumn('data_kesiapan_bekerja', 'bersedia_training')) {
            DB::statement("
                ALTER TABLE data_kesiapan_bekerja
                ALTER COLUMN bersedia_training TYPE varchar(255)
                USING bersedia_training::varchar
            ");
        }
    }

    public function down(): void
    {
        // Constraint lama tidak dibuat ulang karena form baru memakai pilihan Google Form.
    }
};