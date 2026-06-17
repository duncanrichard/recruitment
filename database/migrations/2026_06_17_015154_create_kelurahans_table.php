<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelurahan', function (Blueprint $table) {
            $table->text('id')->primary();
            $table->text('kecamatan_id');
            $table->text('nama');
            $table->timestamp('created_at', 3)->nullable();
            $table->timestamp('updated_at', 3)->nullable();

            $table->foreign('kecamatan_id')
                ->references('id')
                ->on('kecamatan')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->index('kecamatan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelurahan');
    }
};