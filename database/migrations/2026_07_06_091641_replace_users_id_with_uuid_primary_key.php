<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus constraint primary key lama jika masih pakai users.id
        DB::statement("
            ALTER TABLE public.users
            DROP CONSTRAINT IF EXISTS users_pkey CASCADE;
        ");

        // Pastikan kolom uuid tidak null
        DB::statement("
            UPDATE public.users
            SET uuid = gen_random_uuid()
            WHERE uuid IS NULL;
        ");

        DB::statement("
            ALTER TABLE public.users
            ALTER COLUMN uuid SET NOT NULL;
        ");

        // Hapus kolom id
        DB::statement("
            ALTER TABLE public.users
            DROP COLUMN IF EXISTS id CASCADE;
        ");

        // Jadikan uuid sebagai primary key
        DB::statement("
            ALTER TABLE public.users
            ADD CONSTRAINT users_pkey PRIMARY KEY (uuid);
        ");
    }

    public function down(): void
    {
        // Balikin id jika rollback
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'id')) {
                $table->uuid('id')->nullable();
            }
        });

        DB::statement("
            ALTER TABLE public.users
            DROP CONSTRAINT IF EXISTS users_pkey CASCADE;
        ");

        DB::statement("
            ALTER TABLE public.users
            ADD CONSTRAINT users_pkey PRIMARY KEY (uuid);
        ");
    }
};