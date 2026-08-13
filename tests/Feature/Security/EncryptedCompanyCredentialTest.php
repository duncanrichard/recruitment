<?php

namespace Tests\Feature\Security;

use App\Models\DataPerusahaan;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EncryptedCompanyCredentialTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('data_perusahaan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_perusahaan')->nullable();
            $table->string('token_api_wa')->nullable();
            $table->text('token_api_wa_ciphertext')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('data_perusahaan');
        parent::tearDown();
    }

    public function test_whatsapp_token_is_encrypted_and_hidden_from_json(): void
    {
        $company = DataPerusahaan::query()->create([
            'nama_perusahaan' => 'Company A',
            'token_api_wa' => 'secret-token',
        ]);

        $this->assertNull($company->getRawOriginal('token_api_wa'));
        $this->assertSame('secret-token', $company->token_api_wa);
        $this->assertNotSame('secret-token', $company->token_api_wa_ciphertext);
        $this->assertArrayNotHasKey('token_api_wa', $company->toArray());
        $this->assertArrayNotHasKey('token_api_wa_ciphertext', $company->toArray());
    }
}
