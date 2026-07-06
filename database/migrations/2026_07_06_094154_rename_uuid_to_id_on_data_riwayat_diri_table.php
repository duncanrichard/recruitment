<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS pgcrypto');

        // Kalau kolom uuid belum ada dan id juga belum ada, buat id langsung.
        DB::statement("
            ALTER TABLE public.data_riwayat_diri
            ADD COLUMN IF NOT EXISTS uuid uuid;
        ");

        DB::statement("
            UPDATE public.data_riwayat_diri
            SET uuid = gen_random_uuid()
            WHERE uuid IS NULL;
        ");

        DB::statement("
            ALTER TABLE public.data_riwayat_diri
            ALTER COLUMN uuid SET NOT NULL;
        ");

        // Hapus constraint lama jika ada.
        DB::statement("
            ALTER TABLE public.data_riwayat_diri
            DROP CONSTRAINT IF EXISTS data_riwayat_diri_pkey CASCADE;
        ");

        DB::statement("
            ALTER TABLE public.data_riwayat_diri
            DROP CONSTRAINT IF EXISTS data_riwayat_diri_uuid_unique;
        ");

        // Kalau kolom id sudah ada, hapus dulu agar rename tidak bentrok.
        DB::statement("
            ALTER TABLE public.data_riwayat_diri
            DROP COLUMN IF EXISTS id CASCADE;
        ");

        // Rename uuid menjadi id.
        DB::statement("
            ALTER TABLE public.data_riwayat_diri
            RENAME COLUMN uuid TO id;
        ");

        // Jadikan id sebagai primary key.
        DB::statement("
            ALTER TABLE public.data_riwayat_diri
            ADD CONSTRAINT data_riwayat_diri_pkey PRIMARY KEY (id);
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE public.data_riwayat_diri
            DROP CONSTRAINT IF EXISTS data_riwayat_diri_pkey CASCADE;
        ");

        DB::statement("
            ALTER TABLE public.data_riwayat_diri
            RENAME COLUMN id TO uuid;
        ");

        DB::statement("
            ALTER TABLE public.data_riwayat_diri
            ADD CONSTRAINT data_riwayat_diri_uuid_unique UNIQUE (uuid);
        ");
    }
};