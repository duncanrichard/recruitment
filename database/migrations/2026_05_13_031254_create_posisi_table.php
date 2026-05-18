<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posisi', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('nama_posisi');
            $table->text('deskripsi')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();

            $table->uuid('deleted_by')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posisi');
    }
};