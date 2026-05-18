<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS "pgcrypto"');

        Schema::create('sosial_media', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));

            $table->uuid('data_riwayat_diri_id')->nullable();

            $table->string('platform', 100)->nullable();

            $table->string('nama_account', 255)->nullable();

            $table->timestamps();

            $table->softDeletes();

            $table->foreign('data_riwayat_diri_id')
                ->references('id')
                ->on('data_riwayat_diri')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sosial_media');
    }
};