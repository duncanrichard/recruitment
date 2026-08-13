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
        Schema::table('data_perusahaan', function (Blueprint $table) {
            $table->text('token_api_wa_ciphertext')->nullable()->after('token_api_wa');
        });

        DB::table('data_perusahaan')
            ->whereNotNull('token_api_wa')
            ->orderBy('id')
            ->chunk(200, function ($companies) {
                foreach ($companies as $company) {
                    $token = trim((string) $company->token_api_wa);

                    if ($token === '') {
                        continue;
                    }

                    DB::table('data_perusahaan')->where('id', $company->id)->update([
                        'token_api_wa' => null,
                        'token_api_wa_ciphertext' => Crypt::encryptString($token),
                    ]);
                }
            });
    }

    public function down(): void
    {
        DB::table('data_perusahaan')
            ->whereNotNull('token_api_wa_ciphertext')
            ->orderBy('id')
            ->chunk(200, function ($companies) {
                foreach ($companies as $company) {
                    DB::table('data_perusahaan')->where('id', $company->id)->update([
                        'token_api_wa' => Crypt::decryptString($company->token_api_wa_ciphertext),
                    ]);
                }
            });

        Schema::table('data_perusahaan', function (Blueprint $table) {
            $table->dropColumn('token_api_wa_ciphertext');
        });
    }
};
