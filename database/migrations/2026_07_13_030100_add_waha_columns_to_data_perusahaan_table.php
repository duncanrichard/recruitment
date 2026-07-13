<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_perusahaan', function (Blueprint $table) {
            if (!Schema::hasColumn('data_perusahaan', 'wa_session_name')) {
                $table->string('wa_session_name', 150)
                    ->nullable()
                    ->unique();
            }

            if (!Schema::hasColumn('data_perusahaan', 'wa_device_number')) {
                $table->string('wa_device_number', 30)
                    ->nullable();
            }

            if (!Schema::hasColumn('data_perusahaan', 'wa_connected_at')) {
                $table->timestampTz('wa_connected_at')
                    ->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('data_perusahaan', function (Blueprint $table) {
            if (Schema::hasColumn('data_perusahaan', 'wa_session_name')) {
                $table->dropUnique(
                    'data_perusahaan_wa_session_name_unique'
                );

                $table->dropColumn('wa_session_name');
            }

            if (Schema::hasColumn('data_perusahaan', 'wa_device_number')) {
                $table->dropColumn('wa_device_number');
            }

            if (Schema::hasColumn('data_perusahaan', 'wa_connected_at')) {
                $table->dropColumn('wa_connected_at');
            }
        });
    }
};