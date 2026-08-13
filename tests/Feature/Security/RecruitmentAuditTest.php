<?php

namespace Tests\Feature\Security;

use App\Models\DataRiwayatDiri;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RecruitmentAuditTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('data_riwayat_diri', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('token')->nullable();
            $table->string('nama_lengkap')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('recruitment_audits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('auditable_type');
            $table->string('auditable_id');
            $table->string('event');
            $table->uuid('user_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('recruitment_audits');
        Schema::dropIfExists('data_riwayat_diri');

        parent::tearDown();
    }

    public function test_candidate_changes_are_audited_without_access_tokens(): void
    {
        $candidate = DataRiwayatDiri::query()->create(['nama_lengkap' => 'Awal']);
        $candidate->update(['nama_lengkap' => 'Baru']);

        $audit = DB::table('recruitment_audits')
            ->where('auditable_id', $candidate->id)
            ->where('event', 'updated')
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame('Awal', json_decode($audit->old_values, true)['nama_lengkap']);
        $this->assertSame('Baru', json_decode($audit->new_values, true)['nama_lengkap']);
        $this->assertStringNotContainsString('token', (string) $audit->new_values);
    }

    public function test_query_builder_mutations_and_exports_are_audited(): void
    {
        $candidate = DataRiwayatDiri::query()->create(['nama_lengkap' => 'Awal']);

        DB::table('data_riwayat_diri')->where('id', $candidate->id)->update([
            'nama_lengkap' => 'Query Builder',
        ]);

        $this->assertTrue(
            DB::table('recruitment_audits')
                ->where('auditable_type', 'database_table')
                ->where('auditable_id', 'data_riwayat_diri')
                ->where('event', 'db_update')
                ->exists()
        );

        Route::get('/audit-export-test', fn () => response('ok'))
            ->middleware('audit.access:exported')
            ->name('audit.export.test');

        $this->get('/audit-export-test')->assertOk();

        $this->assertTrue(
            DB::table('recruitment_audits')
                ->where('auditable_type', 'http_route')
                ->where('auditable_id', 'audit.export.test')
                ->where('event', 'exported')
                ->exists()
        );
    }
}
