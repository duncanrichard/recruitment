<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_perusahaan_user', function (Blueprint $table) {
            $table->id();

            $table->uuid('user_id');
            $table->uuid('perusahaan_id');

            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('perusahaan_id')
                ->references('id')
                ->on('data_perusahaan')
                ->cascadeOnDelete();

            $table->unique(['user_id', 'perusahaan_id'], 'data_perusahaan_user_unique');
        });

        /*
         * Optional:
         * Kalau sebelumnya sudah ada users.perusahaan_id,
         * data lama akan dipindahkan ke pivot.
         */
        if (Schema::hasColumn('users', 'perusahaan_id')) {
            $rows = DB::table('users')
                ->whereNotNull('perusahaan_id')
                ->select('id', 'perusahaan_id')
                ->get();

            foreach ($rows as $row) {
                DB::table('data_perusahaan_user')->updateOrInsert([
                    'user_id' => $row->id,
                    'perusahaan_id' => $row->perusahaan_id,
                ], [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('data_perusahaan_user');
    }
};