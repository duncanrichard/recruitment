<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_deliveries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('idempotency_key', 64)->unique();
            $table->string('provider', 50);
            $table->uuid('company_id')->nullable();
            $table->string('status', 30)->default('queued');
            $table->unsignedInteger('attempts')->default(0);
            $table->unsignedInteger('item_count')->default(0);
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->text('error_message')->nullable();
            $table->json('provider_response')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['provider', 'status']);
            $table->index(['company_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_deliveries');
    }
};
