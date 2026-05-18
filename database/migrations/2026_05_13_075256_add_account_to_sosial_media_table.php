<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sosial_media', function (Blueprint $table) {
            if (!Schema::hasColumn('sosial_media', 'account')) {
                $table->string('account', 255)
                    ->nullable()
                    ->after('platform');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sosial_media', function (Blueprint $table) {
            if (Schema::hasColumn('sosial_media', 'account')) {
                $table->dropColumn('account');
            }
        });
    }
};