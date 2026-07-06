<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE public.data_riwayat_diri
            ADD CONSTRAINT data_riwayat_diri_created_by_foreign
            FOREIGN KEY (created_by)
            REFERENCES public.users(id)
            ON DELETE SET NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE public.data_riwayat_diri
            DROP CONSTRAINT IF EXISTS data_riwayat_diri_created_by_foreign
        ");
    }
};