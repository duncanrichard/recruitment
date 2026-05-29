<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS "pgcrypto"');

        if (!Schema::hasColumn('users', 'uuid')) {
            Schema::table('users', function (Blueprint $table) {
                $table->uuid('uuid')->nullable()->after('id');
            });
        }

        DB::statement('UPDATE users SET uuid = gen_random_uuid() WHERE uuid IS NULL');

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->unique('uuid');
            });
        } catch (\Throwable $e) {
            //
        }

        DB::statement('ALTER TABLE users ALTER COLUMN uuid SET NOT NULL');
        DB::statement('ALTER TABLE users ALTER COLUMN uuid SET DEFAULT gen_random_uuid()');

        if (!Schema::hasColumn('users', 'role_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('role_id')
                    ->nullable()
                    ->after('remember_token')
                    ->constrained('roles')
                    ->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('users', 'divisi_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->uuid('divisi_id')->nullable()->after('role_id');
            });
        }

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('divisi_id')
                    ->references('id')
                    ->on('divisi')
                    ->nullOnDelete();
            });
        } catch (\Throwable $e) {
            //
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'divisi_id')) {
            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropForeign(['divisi_id']);
                });
            } catch (\Throwable $e) {
                //
            }

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('divisi_id');
            });
        }

        if (Schema::hasColumn('users', 'role_id')) {
            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropForeign(['role_id']);
                });
            } catch (\Throwable $e) {
                //
            }

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role_id');
            });
        }

        if (Schema::hasColumn('users', 'uuid')) {
            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropUnique(['uuid']);
                });
            } catch (\Throwable $e) {
                //
            }

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('uuid');
            });
        }
    }
};