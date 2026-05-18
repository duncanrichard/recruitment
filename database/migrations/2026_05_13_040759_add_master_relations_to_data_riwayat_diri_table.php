<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE data_riwayat_diri
            DROP CONSTRAINT IF EXISTS data_riwayat_diri_posisi_yang_dilamar_foreign
        ");

        DB::statement("
            ALTER TABLE data_riwayat_diri
            DROP CONSTRAINT IF EXISTS data_riwayat_diri_perusahaan_dilamar_foreign
        ");

        DB::statement("
            ALTER TABLE data_riwayat_diri
            DROP CONSTRAINT IF EXISTS data_riwayat_diri_pendidikan_id_foreign
        ");

        DB::statement("
            ALTER TABLE data_riwayat_diri
            DROP CONSTRAINT IF EXISTS data_riwayat_diri_agama_id_foreign
        ");

        DB::statement("
            ALTER TABLE data_riwayat_diri
            DROP CONSTRAINT IF EXISTS data_riwayat_diri_kewarganegaraan_id_foreign
        ");

        DB::statement("
            ALTER TABLE data_riwayat_diri
            DROP CONSTRAINT IF EXISTS data_riwayat_diri_status_pernikahan_id_foreign
        ");

        DB::statement("
            ALTER TABLE data_riwayat_diri
            DROP CONSTRAINT IF EXISTS data_riwayat_diri_sosial_media_id_foreign
        ");

        DB::statement("
            ALTER TABLE data_riwayat_diri
            ALTER COLUMN posisi_yang_dilamar TYPE uuid
            USING posisi_yang_dilamar::uuid
        ");

        DB::statement("
            ALTER TABLE data_riwayat_diri
            ALTER COLUMN perusahaan_dilamar TYPE uuid
            USING perusahaan_dilamar::uuid
        ");

        DB::statement("
            ALTER TABLE data_riwayat_diri
            ALTER COLUMN pendidikan_id TYPE uuid
            USING pendidikan_id::uuid
        ");

        DB::statement("
            ALTER TABLE data_riwayat_diri
            ALTER COLUMN agama_id TYPE uuid
            USING agama_id::uuid
        ");

        DB::statement("
            ALTER TABLE data_riwayat_diri
            ALTER COLUMN kewarganegaraan_id TYPE uuid
            USING kewarganegaraan_id::uuid
        ");

        DB::statement("
            ALTER TABLE data_riwayat_diri
            ALTER COLUMN status_pernikahan_id TYPE uuid
            USING status_pernikahan_id::uuid
        ");

        DB::statement("
            ALTER TABLE data_riwayat_diri
            ALTER COLUMN sosial_media_id TYPE uuid
            USING sosial_media_id::uuid
        ");

        DB::statement("
            ALTER TABLE data_riwayat_diri
            ADD CONSTRAINT data_riwayat_diri_posisi_yang_dilamar_foreign
            FOREIGN KEY (posisi_yang_dilamar)
            REFERENCES posisi(id)
            ON DELETE SET NULL
        ");

        DB::statement("
            ALTER TABLE data_riwayat_diri
            ADD CONSTRAINT data_riwayat_diri_perusahaan_dilamar_foreign
            FOREIGN KEY (perusahaan_dilamar)
            REFERENCES data_perusahaan(id)
            ON DELETE SET NULL
        ");

        DB::statement("
            ALTER TABLE data_riwayat_diri
            ADD CONSTRAINT data_riwayat_diri_pendidikan_id_foreign
            FOREIGN KEY (pendidikan_id)
            REFERENCES pendidikan(id)
            ON DELETE SET NULL
        ");

        DB::statement("
            ALTER TABLE data_riwayat_diri
            ADD CONSTRAINT data_riwayat_diri_agama_id_foreign
            FOREIGN KEY (agama_id)
            REFERENCES agama(id)
            ON DELETE SET NULL
        ");

        DB::statement("
            ALTER TABLE data_riwayat_diri
            ADD CONSTRAINT data_riwayat_diri_kewarganegaraan_id_foreign
            FOREIGN KEY (kewarganegaraan_id)
            REFERENCES kewarganegaraan(id)
            ON DELETE SET NULL
        ");

        DB::statement("
            ALTER TABLE data_riwayat_diri
            ADD CONSTRAINT data_riwayat_diri_status_pernikahan_id_foreign
            FOREIGN KEY (status_pernikahan_id)
            REFERENCES status_pernikahan(id)
            ON DELETE SET NULL
        ");

        DB::statement("
            ALTER TABLE data_riwayat_diri
            ADD CONSTRAINT data_riwayat_diri_sosial_media_id_foreign
            FOREIGN KEY (sosial_media_id)
            REFERENCES sosial_media(id)
            ON DELETE SET NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE data_riwayat_diri
            DROP CONSTRAINT IF EXISTS data_riwayat_diri_posisi_yang_dilamar_foreign
        ");

        DB::statement("
            ALTER TABLE data_riwayat_diri
            DROP CONSTRAINT IF EXISTS data_riwayat_diri_perusahaan_dilamar_foreign
        ");

        DB::statement("
            ALTER TABLE data_riwayat_diri
            DROP CONSTRAINT IF EXISTS data_riwayat_diri_pendidikan_id_foreign
        ");

        DB::statement("
            ALTER TABLE data_riwayat_diri
            DROP CONSTRAINT IF EXISTS data_riwayat_diri_agama_id_foreign
        ");

        DB::statement("
            ALTER TABLE data_riwayat_diri
            DROP CONSTRAINT IF EXISTS data_riwayat_diri_kewarganegaraan_id_foreign
        ");

        DB::statement("
            ALTER TABLE data_riwayat_diri
            DROP CONSTRAINT IF EXISTS data_riwayat_diri_status_pernikahan_id_foreign
        ");

        DB::statement("
            ALTER TABLE data_riwayat_diri
            DROP CONSTRAINT IF EXISTS data_riwayat_diri_sosial_media_id_foreign
        ");
    }
};