<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS "pgcrypto";');

        Schema::table('interviewers', function (Blueprint $table) {
            if (!Schema::hasColumn('interviewers', 'jabatan_id')) {
                $table->uuid('jabatan_id')->nullable()->after('nama');
            }

            if (!Schema::hasColumn('interviewers', 'divisi_id')) {
                $table->uuid('divisi_id')->nullable()->after('jabatan_id');
            }
        });

        // Pindahkan data jabatan lama ke tabel jabatan
        if (Schema::hasColumn('interviewers', 'jabatan')) {
            DB::statement("
                INSERT INTO jabatan (id, nama, created_at, updated_at)
                SELECT gen_random_uuid(), jabatan, NOW(), NOW()
                FROM interviewers
                WHERE jabatan IS NOT NULL AND jabatan <> ''
                GROUP BY jabatan
                ON CONFLICT (nama) DO NOTHING
            ");

            DB::statement("
                UPDATE interviewers i
                SET jabatan_id = j.id
                FROM jabatan j
                WHERE i.jabatan = j.nama
            ");
        }

        // Pindahkan data divisi lama ke tabel divisi
        if (Schema::hasColumn('interviewers', 'divisi')) {
            DB::statement("
                INSERT INTO divisi (id, nama, created_at, updated_at)
                SELECT gen_random_uuid(), divisi, NOW(), NOW()
                FROM interviewers
                WHERE divisi IS NOT NULL AND divisi <> ''
                GROUP BY divisi
                ON CONFLICT (nama) DO NOTHING
            ");

            DB::statement("
                UPDATE interviewers i
                SET divisi_id = d.id
                FROM divisi d
                WHERE i.divisi = d.nama
            ");
        }

        Schema::table('interviewers', function (Blueprint $table) {
            if (Schema::hasColumn('interviewers', 'jabatan')) {
                $table->dropColumn('jabatan');
            }

            if (Schema::hasColumn('interviewers', 'divisi')) {
                $table->dropColumn('divisi');
            }
        });

        Schema::table('interviewers', function (Blueprint $table) {
            $table->foreign('jabatan_id')
                ->references('id')
                ->on('jabatan')
                ->nullOnDelete();

            $table->foreign('divisi_id')
                ->references('id')
                ->on('divisi')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('interviewers', function (Blueprint $table) {
            $table->dropForeign(['jabatan_id']);
            $table->dropForeign(['divisi_id']);
        });

        Schema::table('interviewers', function (Blueprint $table) {
            $table->string('jabatan')->nullable();
            $table->string('divisi')->nullable();
        });

        DB::statement("
            UPDATE interviewers i
            SET jabatan = j.nama
            FROM jabatan j
            WHERE i.jabatan_id = j.id
        ");

        DB::statement("
            UPDATE interviewers i
            SET divisi = d.nama
            FROM divisi d
            WHERE i.divisi_id = d.id
        ");

        Schema::table('interviewers', function (Blueprint $table) {
            $table->dropColumn('jabatan_id');
            $table->dropColumn('divisi_id');
        });
    }
};