<?php

namespace Tests\Feature\Security;

use App\Models\DataRiwayatDiri;
use App\Models\User;
use App\Policies\DataRiwayatDiriPolicy;
use App\Services\CompanyAccessService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CompanyAccessServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('company_records', function (Blueprint $table) {
            $table->id();
            $table->string('company_id');
        });

        DB::table('company_records')->insert([
            ['company_id' => 'company-a'],
            ['company_id' => 'company-b'],
        ]);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('company_records');

        parent::tearDown();
    }

    public function test_company_filter_only_returns_authorized_records(): void
    {
        $user = new class extends User
        {
            public function hasAnyRole(...$roles): bool
            {
                return false;
            }

            public function perusahaans(): object
            {
                return new class
                {
                    public function pluck(): Collection
                    {
                        return collect(['company-a']);
                    }
                };
            }
        };

        $query = DB::table('company_records');
        $service = app(CompanyAccessService::class);
        $service->apply($query, $user, 'company_id');

        $this->assertSame(['company-a'], $query->pluck('company_id')->all());
        $this->assertTrue($service->canAccess($user, 'company-a'));
        $this->assertFalse($service->canAccess($user, 'company-b'));
    }

    public function test_unauthenticated_access_returns_no_company_records(): void
    {
        $query = DB::table('company_records');
        app(CompanyAccessService::class)->apply($query, null, 'company_id');

        $this->assertCount(0, $query->get());
    }

    public function test_candidate_policy_denies_cross_company_access(): void
    {
        $user = new class extends User
        {
            public function hasAnyRole(...$roles): bool
            {
                return false;
            }

            public function perusahaans(): object
            {
                return new class
                {
                    public function pluck(): Collection
                    {
                        return collect(['company-a']);
                    }
                };
            }
        };

        $policy = new DataRiwayatDiriPolicy;
        $ownCandidate = new DataRiwayatDiri(['perusahaan_dilamar' => 'company-a']);
        $otherCandidate = new DataRiwayatDiri(['perusahaan_dilamar' => 'company-b']);

        $this->assertTrue($policy->view($user, $ownCandidate));
        $this->assertFalse($policy->view($user, $otherCandidate));
        $this->assertFalse($policy->update($user, $otherCandidate));
        $this->assertFalse($policy->delete($user, $otherCandidate));
    }
}
