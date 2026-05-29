<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * PostgreSQL butuh extension ini untuk generate UUID.
         */
        DB::statement('CREATE EXTENSION IF NOT EXISTS "pgcrypto"');

        /**
         * Kalau tabel users sudah ada data, cara aman:
         * 1. Tambah kolom uuid sementara
         * 2. Isi dengan gen_random_uuid()
         * 3. Hapus primary key lama
         * 4. Hapus kolom id lama
         * 5. Rename uuid menjadi id
         * 6. Jadikan primary key baru
         */
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
        });

        DB::statement('UPDATE users SET uuid = gen_random_uuid() WHERE uuid IS NULL');

        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_pkey');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        DB::statement('ALTER TABLE users RENAME COLUMN uuid TO id');

        DB::statement('ALTER TABLE users ALTER COLUMN id SET NOT NULL');
        DB::statement('ALTER TABLE users ADD PRIMARY KEY (id)');

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')
                ->nullable()
                ->after('remember_token')
                ->constrained('roles')
                ->nullOnDelete();

            $table->foreignId('divisi_id')
                ->nullable()
                ->after('role_id')
                ->constrained('divisis')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropForeign(['divisi_id']);
            $table->dropColumn(['role_id', 'divisi_id']);
        });

        /**
         * Rollback UUID ke bigserial.
         * Data id UUID lama akan hilang dan diganti id integer baru.
         */
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_pkey');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        DB::statement('CREATE SEQUENCE users_id_seq');

        Schema::table('users', function (Blueprint $table) {
            $table->bigInteger('id')->nullable();
        });

        DB::statement("ALTER TABLE users ALTER COLUMN id SET DEFAULT nextval('users_id_seq')");
        DB::statement("UPDATE users SET id = nextval('users_id_seq') WHERE id IS NULL");
        DB::statement('ALTER TABLE users ALTER COLUMN id SET NOT NULL');
        DB::statement('ALTER TABLE users ADD PRIMARY KEY (id)');
        DB::statement("ALTER SEQUENCE users_id_seq OWNED BY users.id");
    }
};