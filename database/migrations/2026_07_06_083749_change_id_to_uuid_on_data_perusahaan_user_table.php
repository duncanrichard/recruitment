<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS pgcrypto');

        DB::transaction(function () {
            DB::statement('ALTER TABLE data_perusahaan_user DROP CONSTRAINT IF EXISTS data_perusahaan_user_pkey');

            DB::statement('ALTER TABLE data_perusahaan_user ADD COLUMN id_uuid uuid DEFAULT gen_random_uuid()');

            DB::statement('UPDATE data_perusahaan_user SET id_uuid = gen_random_uuid() WHERE id_uuid IS NULL');

            DB::statement('ALTER TABLE data_perusahaan_user DROP COLUMN id');

            DB::statement('ALTER TABLE data_perusahaan_user RENAME COLUMN id_uuid TO id');

            DB::statement('ALTER TABLE data_perusahaan_user ALTER COLUMN id SET NOT NULL');

            DB::statement('ALTER TABLE data_perusahaan_user ADD CONSTRAINT data_perusahaan_user_pkey PRIMARY KEY (id)');
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            DB::statement('ALTER TABLE data_perusahaan_user DROP CONSTRAINT IF EXISTS data_perusahaan_user_pkey');

            DB::statement('ALTER TABLE data_perusahaan_user ADD COLUMN id_bigint bigserial');

            DB::statement('ALTER TABLE data_perusahaan_user DROP COLUMN id');

            DB::statement('ALTER TABLE data_perusahaan_user RENAME COLUMN id_bigint TO id');

            DB::statement('ALTER TABLE data_perusahaan_user ADD CONSTRAINT data_perusahaan_user_pkey PRIMARY KEY (id)');
        });
    }
};