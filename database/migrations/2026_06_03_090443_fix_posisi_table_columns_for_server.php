<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('posisi')) {
            DB::statement("
                DO $$
                BEGIN
                    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'posisi_str_aktif_enum') THEN
                        CREATE TYPE posisi_str_aktif_enum AS ENUM ('active', 'non_active');
                    END IF;
                END$$;
            ");

            Schema::create('posisi', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('nama_posisi', 255);
                $table->text('deskripsi')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->timestamp('deleted_at')->nullable();
                $table->uuid('deleted_by')->nullable();
                $table->string('kode_posisi', 50)->nullable();
            });

            DB::statement("
                ALTER TABLE posisi
                ADD COLUMN str_aktif posisi_str_aktif_enum NOT NULL DEFAULT 'non_active'
            ");

            return;
        }

        DB::statement("
            DO $$
            BEGIN
                IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'posisi_str_aktif_enum') THEN
                    CREATE TYPE posisi_str_aktif_enum AS ENUM ('active', 'non_active');
                END IF;
            END$$;
        ");

        Schema::table('posisi', function (Blueprint $table) {
            if (! Schema::hasColumn('posisi', 'id')) {
                $table->uuid('id')->primary();
            }

            if (! Schema::hasColumn('posisi', 'nama_posisi')) {
                $table->string('nama_posisi', 255)->nullable();
            }

            if (! Schema::hasColumn('posisi', 'deskripsi')) {
                $table->text('deskripsi')->nullable();
            }

            if (! Schema::hasColumn('posisi', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }

            if (! Schema::hasColumn('posisi', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }

            if (! Schema::hasColumn('posisi', 'deleted_at')) {
                $table->timestamp('deleted_at')->nullable();
            }

            if (! Schema::hasColumn('posisi', 'deleted_by')) {
                $table->uuid('deleted_by')->nullable();
            }

            if (! Schema::hasColumn('posisi', 'kode_posisi')) {
                $table->string('kode_posisi', 50)->nullable();
            }
        });

        if (! Schema::hasColumn('posisi', 'str_aktif')) {
            DB::statement("
                ALTER TABLE posisi
                ADD COLUMN str_aktif posisi_str_aktif_enum NOT NULL DEFAULT 'non_active'
            ");
        }
    }

    public function down(): void
    {
        Schema::table('posisi', function (Blueprint $table) {
            if (Schema::hasColumn('posisi', 'kode_posisi')) {
                $table->dropColumn('kode_posisi');
            }

            if (Schema::hasColumn('posisi', 'deleted_by')) {
                $table->dropColumn('deleted_by');
            }
        });
    }
};