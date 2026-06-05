<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('jadwal_offering_letters')) {
            DB::statement("
                ALTER TABLE jadwal_offering_letters
                ALTER COLUMN status_jadwal DROP NOT NULL
            ");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('jadwal_offering_letters')) {
            DB::statement("
                UPDATE jadwal_offering_letters
                SET status_jadwal = 'Belum Dipilih'
                WHERE status_jadwal IS NULL
            ");

            DB::statement("
                ALTER TABLE jadwal_offering_letters
                ALTER COLUMN status_jadwal SET NOT NULL
            ");
        }
    }
};