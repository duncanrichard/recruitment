<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_interview', function (Blueprint $table) {
            if (!Schema::hasColumn('jadwal_interview', 'google_calendar_event_id')) {
                $table->string('google_calendar_event_id')->nullable()->after('jadwal_interview');
            }

            if (!Schema::hasColumn('jadwal_interview', 'google_calendar_html_link')) {
                $table->text('google_calendar_html_link')->nullable()->after('google_calendar_event_id');
            }

            if (!Schema::hasColumn('jadwal_interview', 'google_meet_link')) {
                $table->text('google_meet_link')->nullable()->after('google_calendar_html_link');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_interview', function (Blueprint $table) {
            if (Schema::hasColumn('jadwal_interview', 'google_calendar_event_id')) {
                $table->dropColumn('google_calendar_event_id');
            }

            if (Schema::hasColumn('jadwal_interview', 'google_calendar_html_link')) {
                $table->dropColumn('google_calendar_html_link');
            }

            if (Schema::hasColumn('jadwal_interview', 'google_meet_link')) {
                $table->dropColumn('google_meet_link');
            }
        });
    }
};