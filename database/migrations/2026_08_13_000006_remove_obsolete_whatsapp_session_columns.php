<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const COLUMNS = [
        'wa_session_name',
        'wa_device_number',
        'wa_connected_at',
    ];

    public function up(): void
    {
        Schema::table('data_perusahaan', function (Blueprint $table) {
            foreach (self::COLUMNS as $column) {
                if (Schema::hasColumn('data_perusahaan', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('data_perusahaan', function (Blueprint $table) {
            if (! Schema::hasColumn('data_perusahaan', 'wa_session_name')) {
                $table->string('wa_session_name', 150)->nullable()->unique();
            }

            if (! Schema::hasColumn('data_perusahaan', 'wa_device_number')) {
                $table->string('wa_device_number', 30)->nullable();
            }

            if (! Schema::hasColumn('data_perusahaan', 'wa_connected_at')) {
                $table->timestampTz('wa_connected_at')->nullable();
            }
        });
    }
};
