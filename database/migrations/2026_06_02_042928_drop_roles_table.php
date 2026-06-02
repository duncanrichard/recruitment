<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * PostgreSQL aman:
         * Kalau constraint ada, dihapus.
         * Kalau tidak ada, tidak error.
         */
        if (Schema::hasTable('users')) {
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_id_foreign');
        }

        /*
         * Kalau kolom role_id lama masih ada di users dan mau dihapus,
         * aktifkan bagian ini.
         *
         * Catatan:
         * Kalau kamu masih butuh role_id lama, jangan aktifkan.
         */
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'role_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role_id');
            });
        }

        /*
         * Hapus tabel roles lama.
         * Nanti tabel roles akan dibuat ulang oleh Spatie Permission.
         */
        Schema::dropIfExists('roles');
    }

    public function down(): void
    {
        /*
         * Tidak perlu restore roles lama karena sekarang akan pakai Spatie.
         * Kalau butuh rollback khusus, bisa dibuat ulang manual.
         */
    }
};