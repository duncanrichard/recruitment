<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posisi', function (Blueprint $table) {
            $table->string('kode_posisi', 50)
                ->nullable()
                ->after('id');

            $table->unique('kode_posisi');
        });
    }

    public function down(): void
    {
        Schema::table('posisi', function (Blueprint $table) {
            $table->dropUnique(['kode_posisi']);
            $table->dropColumn('kode_posisi');
        });
    }
};