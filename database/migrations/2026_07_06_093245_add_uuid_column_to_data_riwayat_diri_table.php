<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS pgcrypto');

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

        DB::statement("
            ALTER TABLE public.data_riwayat_diri
            DROP CONSTRAINT IF EXISTS data_riwayat_diri_uuid_unique;
        ");

        DB::statement("
            ALTER TABLE public.data_riwayat_diri
            ADD CONSTRAINT data_riwayat_diri_uuid_unique UNIQUE (uuid);
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE public.data_riwayat_diri
            DROP CONSTRAINT IF EXISTS data_riwayat_diri_uuid_unique;
        ");

        DB::statement("
            ALTER TABLE public.data_riwayat_diri
            DROP COLUMN IF EXISTS uuid;
        ");
    }
};