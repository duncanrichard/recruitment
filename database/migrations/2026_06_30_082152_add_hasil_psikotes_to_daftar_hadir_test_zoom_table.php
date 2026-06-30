<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daftar_hadir_test_zoom', function (Blueprint $table) {
            if (!Schema::hasColumn('daftar_hadir_test_zoom', 'hasil_test_iq')) {
                $table->string('hasil_test_iq')->nullable()->after('hasil_test');
            }

            if (!Schema::hasColumn('daftar_hadir_test_zoom', 'hasil_test_disc')) {
                $table->string('hasil_test_disc')->nullable()->after('hasil_test_iq');
            }

            if (!Schema::hasColumn('daftar_hadir_test_zoom', 'hasil_test_eysenck')) {
                $table->string('hasil_test_eysenck')->nullable()->after('hasil_test_disc');
            }
        });
    }

    public function down(): void
    {
        Schema::table('daftar_hadir_test_zoom', function (Blueprint $table) {
            if (Schema::hasColumn('daftar_hadir_test_zoom', 'hasil_test_eysenck')) {
                $table->dropColumn('hasil_test_eysenck');
            }

            if (Schema::hasColumn('daftar_hadir_test_zoom', 'hasil_test_disc')) {
                $table->dropColumn('hasil_test_disc');
            }

            if (Schema::hasColumn('daftar_hadir_test_zoom', 'hasil_test_iq')) {
                $table->dropColumn('hasil_test_iq');
            }
        });
    }
};
