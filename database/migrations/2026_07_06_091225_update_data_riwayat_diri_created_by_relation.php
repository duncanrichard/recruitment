<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        // Pastikan users.uuid unique
        DB::statement("
            DO $$
            BEGIN
                IF NOT EXISTS (
                    SELECT 1
                    FROM pg_constraint
                    WHERE conname = 'users_uuid_unique'
                ) THEN
                    ALTER TABLE public.users
                    ADD CONSTRAINT users_uuid_unique UNIQUE (uuid);
                END IF;
            END $$;
        ");

        // Hapus field id dari data_riwayat_diri beserta relasi lama yang bergantung ke id
        DB::statement("
            ALTER TABLE public.data_riwayat_diri
            DROP COLUMN IF EXISTS id CASCADE;
        ");

        // Tambah created_by kalau belum ada
        Schema::table('data_riwayat_diri', function (Blueprint $table) {
            if (!Schema::hasColumn('data_riwayat_diri', 'created_by')) {
                $table->uuid('created_by')->nullable();
            }
        });

        // Bersihkan data created_by yang tidak ada di users.uuid
        DB::statement("
            UPDATE public.data_riwayat_diri d
            SET created_by = NULL
            WHERE created_by IS NOT NULL
            AND NOT EXISTS (
                SELECT 1
                FROM public.users u
                WHERE u.uuid = d.created_by
            );
        ");

        // Hapus constraint jika sempat terbentuk sebagian
        DB::statement("
            ALTER TABLE public.data_riwayat_diri
            DROP CONSTRAINT IF EXISTS data_riwayat_diri_created_by_fk;
        ");

        // Buat relasi created_by ke users.uuid
        DB::statement("
            ALTER TABLE public.data_riwayat_diri
            ADD CONSTRAINT data_riwayat_diri_created_by_fk
            FOREIGN KEY (created_by)
            REFERENCES public.users(uuid)
            ON UPDATE CASCADE
            ON DELETE SET NULL;
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE public.data_riwayat_diri
            DROP CONSTRAINT IF EXISTS data_riwayat_diri_created_by_fk;
        ");

        Schema::table('data_riwayat_diri', function (Blueprint $table) {
            if (Schema::hasColumn('data_riwayat_diri', 'created_by')) {
                $table->dropColumn('created_by');
            }

            if (!Schema::hasColumn('data_riwayat_diri', 'id')) {
                $table->uuid('id')->nullable();
            }
        });
    }
};