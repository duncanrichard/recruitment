<?php

namespace Tests\Feature\Security;

use App\Models\DataRiwayatDiri;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class CandidateTokenSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('data_riwayat_diri', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('token', 100)->nullable()->unique();
            $table->string('token_hash', 64)->nullable()->unique();
            $table->text('token_ciphertext')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('data_riwayat_diri');

        parent::tearDown();
    }

    public function test_new_candidate_receives_a_strong_expiring_token(): void
    {
        $candidate = DataRiwayatDiri::query()->create();

        $this->assertMatchesRegularExpression('/^KND-[A-Za-z0-9]{64}$/', $candidate->token);
        $this->assertNull($candidate->getRawOriginal('token'));
        $this->assertSame(hash('sha256', $candidate->token), $candidate->token_hash);
        $this->assertNotSame($candidate->token, $candidate->token_ciphertext);
        $this->assertNotNull($candidate->token_expires_at);
        $this->assertTrue($candidate->token_expires_at->isFuture());
    }

    public function test_expired_token_cannot_be_used_but_legacy_token_remains_compatible(): void
    {
        $expired = DataRiwayatDiri::query()->create([
            'token' => 'expired-token',
            'token_expires_at' => now()->subMinute(),
        ]);
        $legacyId = (string) Str::uuid();
        DB::table('data_riwayat_diri')->insert([
            'id' => $legacyId,
            'token' => 'legacy-token',
            'token_hash' => null,
            'token_ciphertext' => null,
            'token_expires_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $legacy = DataRiwayatDiri::query()->findOrFail($legacyId);

        $this->assertNull(DataRiwayatDiri::query()->withValidToken($expired->token)->first());
        $this->assertTrue(DataRiwayatDiri::query()->withValidToken($legacy->token)->first()?->is($legacy));
    }
}
