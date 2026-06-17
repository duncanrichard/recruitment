<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kecamatan', function (Blueprint $table) {
            $table->text('id')->primary();
            $table->text('kabupaten_id');
            $table->text('nama');
            $table->timestamp('created_at', 3)->nullable();
            $table->timestamp('updated_at', 3)->nullable();

            $table->foreign('kabupaten_id')
                ->references('id')
                ->on('kabupaten')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->index('kabupaten_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kecamatan');
    }
};