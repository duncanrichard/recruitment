<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_riwayat_diri', function (Blueprint $table) {
            $table->string('str_aktif')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('data_riwayat_diri', function (Blueprint $table) {
            $table->string('str_aktif')->nullable(false)->default('active')->change();
        });
    }
};