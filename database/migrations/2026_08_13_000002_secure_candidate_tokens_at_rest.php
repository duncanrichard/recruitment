<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_riwayat_diri', function (Blueprint $table) {
            $table->string('token_hash', 64)->nullable()->unique()->after('token');
            $table->text('token_ciphertext')->nullable()->after('token_hash');
        });

        DB::table('data_riwayat_diri')
            ->whereNotNull('token')
            ->orderBy('id')
            ->chunk(200, function ($candidates) {
                foreach ($candidates as $candidate) {
                    $plainToken = (string) $candidate->token;

                    DB::table('data_riwayat_diri')
                        ->where('id', $candidate->id)
                        ->update([
                            'token' => null,
                            'token_hash' => hash('sha256', $plainToken),
                            'token_ciphertext' => Crypt::encryptString($plainToken),
                        ]);
                }
            });
    }

    public function down(): void
    {
        DB::table('data_riwayat_diri')
            ->whereNotNull('token_ciphertext')
            ->orderBy('id')
            ->chunk(200, function ($candidates) {
                foreach ($candidates as $candidate) {
                    DB::table('data_riwayat_diri')
                        ->where('id', $candidate->id)
                        ->update([
                            'token' => Crypt::decryptString($candidate->token_ciphertext),
                        ]);
                }
            });

        Schema::table('data_riwayat_diri', function (Blueprint $table) {
            $table->dropUnique(['token_hash']);
            $table->dropColumn(['token_hash', 'token_ciphertext']);
        });
    }
};
