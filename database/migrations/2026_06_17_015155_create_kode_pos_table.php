<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kode_pos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 50);
            $table->text('provinsi_id');
            $table->text('kabupaten_id');
            $table->text('kecamatan_id');
            $table->text('kelurahan_id');
            $table->timestamp('created_at', 3)->nullable();

            $table->foreign('provinsi_id')
                ->references('id')
                ->on('provinsi')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('kabupaten_id')
                ->references('id')
                ->on('kabupaten')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('kecamatan_id')
                ->references('id')
                ->on('kecamatan')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('kelurahan_id')
                ->references('id')
                ->on('kelurahan')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->index('kode');
            $table->index('provinsi_id');
            $table->index('kabupaten_id');
            $table->index('kecamatan_id');
            $table->index('kelurahan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kode_pos');
    }
};