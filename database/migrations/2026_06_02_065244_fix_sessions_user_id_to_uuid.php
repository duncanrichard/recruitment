<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->uuid('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });

            return;
        }

        if (Schema::hasColumn('sessions', 'user_id')) {
            DB::statement('ALTER TABLE sessions ALTER COLUMN user_id DROP DEFAULT');
            DB::statement('ALTER TABLE sessions ALTER COLUMN user_id TYPE uuid USING NULL');
        } else {
            Schema::table('sessions', function (Blueprint $table) {
                $table->uuid('user_id')->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sessions') && Schema::hasColumn('sessions', 'user_id')) {
            DB::statement('ALTER TABLE sessions ALTER COLUMN user_id DROP DEFAULT');
            DB::statement('ALTER TABLE sessions ALTER COLUMN user_id TYPE bigint USING NULL');
        }
    }
};