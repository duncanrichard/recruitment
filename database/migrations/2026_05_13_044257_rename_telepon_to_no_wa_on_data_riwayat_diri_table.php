<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE data_riwayat_diri
            RENAME COLUMN telepon TO no_wa
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE data_riwayat_diri
            RENAME COLUMN no_wa TO telepon
        ");
    }
};