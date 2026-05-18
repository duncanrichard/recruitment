<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sumber_informasi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('informasi');
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sumber_informasi');
    }
};