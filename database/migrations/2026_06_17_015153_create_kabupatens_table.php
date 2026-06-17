<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kabupaten', function (Blueprint $table) {
            $table->text('id')->primary();
            $table->text('provinsi_id');
            $table->text('nama');
            $table->timestamp('created_at', 3)->nullable();
            $table->timestamp('updated_at', 3)->nullable();

            $table->foreign('provinsi_id')
                ->references('id')
                ->on('provinsi')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->index('provinsi_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kabupaten');
    }
};