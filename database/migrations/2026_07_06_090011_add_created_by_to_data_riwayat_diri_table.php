<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_riwayat_diri', function (Blueprint $table) {
            if (!Schema::hasColumn('data_riwayat_diri', 'created_by')) {
                $table->uuid('created_by')->nullable()->after('deleted_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('data_riwayat_diri', function (Blueprint $table) {
            if (Schema::hasColumn('data_riwayat_diri', 'created_by')) {
                $table->dropColumn('created_by');
            }
        });
    }
};