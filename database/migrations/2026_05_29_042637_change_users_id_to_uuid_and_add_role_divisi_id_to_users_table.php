<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    private array $deletedByTables = [
        'offering_letter',
        'data_riwayat_kesehatan',
        'data_riwayat_pekerjaan',
        'data_riwayat_keluarga',
        'data_saudara_kandung',
        'data_saudara_ipar',
        'data_kesiapan_bekerja',
    ];

    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS "pgcrypto"');

        /*
         * Pastikan kolom uuid di users sudah ada dan terisi.
         */
        if (!Schema::hasColumn('users', 'uuid')) {
            Schema::table('users', function (Blueprint $table) {
                $table->uuid('uuid')->nullable();
            });
        }

        DB::statement('UPDATE users SET uuid = gen_random_uuid() WHERE uuid IS NULL');

        /*
         * 1. Drop semua foreign key yang masih bergantung ke users.id.
         */
        foreach ($this->deletedByTables as $table) {
            DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$table}_deleted_by_foreign");
        }

        /*
         * 2. Ubah semua kolom deleted_by dari bigint menjadi uuid,
         *    dengan mapping dari users.id lama ke users.uuid.
         */
        foreach ($this->deletedByTables as $table) {
            if (!Schema::hasColumn($table, 'deleted_by')) {
                continue;
            }

            if (!Schema::hasColumn($table, 'deleted_by_uuid')) {
                Schema::table($table, function (Blueprint $tableBlueprint) {
                    $tableBlueprint->uuid('deleted_by_uuid')->nullable();
                });
            }

            DB::statement("
                UPDATE {$table}
                SET deleted_by_uuid = users.uuid
                FROM users
                WHERE {$table}.deleted_by = users.id
            ");

            Schema::table($table, function (Blueprint $tableBlueprint) {
                $tableBlueprint->dropColumn('deleted_by');
            });

            DB::statement("ALTER TABLE {$table} RENAME COLUMN deleted_by_uuid TO deleted_by");
        }

        /*
         * 3. Sekarang users_pkey sudah tidak dipakai foreign key lain.
         *    Aman untuk drop primary key lama.
         */
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_pkey');

        /*
         * 4. Hapus id lama bertipe bigint/bigserial.
         */
        if (Schema::hasColumn('users', 'id')) {
            DB::statement('ALTER TABLE users DROP COLUMN id');
        }

        /*
         * 5. Rename uuid menjadi id.
         */
        if (Schema::hasColumn('users', 'uuid') && !Schema::hasColumn('users', 'id')) {
            DB::statement('ALTER TABLE users RENAME COLUMN uuid TO id');
        }

        /*
         * 6. Jadikan users.id UUID primary key.
         */
        DB::statement('ALTER TABLE users ALTER COLUMN id SET DEFAULT gen_random_uuid()');
        DB::statement('ALTER TABLE users ALTER COLUMN id SET NOT NULL');
        DB::statement('ALTER TABLE users ADD PRIMARY KEY (id)');

        /*
         * 7. Tambahkan ulang foreign key deleted_by ke users.id UUID.
         */
        foreach ($this->deletedByTables as $table) {
            if (!Schema::hasColumn($table, 'deleted_by')) {
                continue;
            }

            DB::statement("
                ALTER TABLE {$table}
                ADD CONSTRAINT {$table}_deleted_by_foreign
                FOREIGN KEY (deleted_by)
                REFERENCES users(id)
                ON DELETE SET NULL
            ");
        }

        /*
         * 8. Tambahkan role_id jika belum ada.
         */
        if (!Schema::hasColumn('users', 'role_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('role_id')->nullable();
            });
        }

        /*
         * 9. Tambahkan divisi_id jika belum ada.
         */
        if (!Schema::hasColumn('users', 'divisi_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->uuid('divisi_id')->nullable();
            });
        }
    }

    public function down(): void
    {
        /*
         * Rollback perubahan UUID ke bigint tidak aman karena data id lama sudah dihapus.
         * Jadi down dibuat minimal agar tidak merusak data.
         */
        foreach ($this->deletedByTables as $table) {
            DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$table}_deleted_by_foreign");
        }

        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_pkey');

        if (Schema::hasColumn('users', 'id') && !Schema::hasColumn('users', 'uuid')) {
            DB::statement('ALTER TABLE users RENAME COLUMN id TO uuid');
        }

        if (!Schema::hasColumn('users', 'id')) {
            DB::statement('ALTER TABLE users ADD COLUMN id BIGSERIAL PRIMARY KEY');
        }
    }
};