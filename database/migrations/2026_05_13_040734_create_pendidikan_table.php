<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS "uuid-ossp";');

        Schema::create('pendidikan', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));

            $table->string('pendidikan');

            $table->timestamp('created_at')->nullable();
            $table->uuid('created_by')->nullable()->index();

            $table->timestamp('updated_at')->nullable();
            $table->uuid('updated_by')->nullable()->index();

            $table->timestamp('deleted_at')->nullable();
            $table->uuid('deleted_by')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendidikan');
    }
};