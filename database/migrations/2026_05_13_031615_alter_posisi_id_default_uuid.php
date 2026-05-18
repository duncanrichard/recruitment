<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS "uuid-ossp";');

        DB::statement("
            ALTER TABLE posisi
            ALTER COLUMN id SET DEFAULT uuid_generate_v4()
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE posisi
            ALTER COLUMN id DROP DEFAULT
        ");
    }
};