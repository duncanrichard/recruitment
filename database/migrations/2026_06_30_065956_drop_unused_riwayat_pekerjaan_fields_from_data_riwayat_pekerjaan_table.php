<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_riwayat_pekerjaan', function (Blueprint $table) {
            foreach ([
                'tahun_mulai_bekerja',
                'tahun_selesai_bekerja',
                'lama_bekerja',
                'catatan_pekerjaan',
            ] as $column) {
                if (Schema::hasColumn('data_riwayat_pekerjaan', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('data_riwayat_pekerjaan', function (Blueprint $table) {
            if (!Schema::hasColumn('data_riwayat_pekerjaan', 'tahun_mulai_bekerja')) {
                $table->string('tahun_mulai_bekerja')->nullable();
            }

            if (!Schema::hasColumn('data_riwayat_pekerjaan', 'tahun_selesai_bekerja')) {
                $table->string('tahun_selesai_bekerja')->nullable();
            }

            if (!Schema::hasColumn('data_riwayat_pekerjaan', 'lama_bekerja')) {
                $table->string('lama_bekerja')->nullable();
            }

            if (!Schema::hasColumn('data_riwayat_pekerjaan', 'catatan_pekerjaan')) {
                $table->text('catatan_pekerjaan')->nullable();
            }
        });
    }
};
