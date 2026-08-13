<?php

namespace Tests\Feature\Security;

use App\Services\MalwareScanner;
use App\Services\RecruitmentWorkflowService;
use App\Services\SpreadsheetValueSanitizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class NegativeFlowCoverageTest extends TestCase
{
    public function test_workflow_accepts_only_the_next_stage(): void
    {
        $workflow = app(RecruitmentWorkflowService::class);
        $stages = RecruitmentWorkflowService::STAGES;

        foreach (array_slice($stages, 0, -1) as $index => $stage) {
            $workflow->assertTransition($stage, $stages[$index + 1]);
            $this->addToAssertionCount(1);
        }
    }

    public function test_workflow_rejects_skipped_and_backward_stages(): void
    {
        $this->expectException(ValidationException::class);
        app(RecruitmentWorkflowService::class)
            ->assertTransition('registered', 'interview_passed');
    }

    public function test_malware_scanner_fails_closed_when_scanner_is_required_but_disabled(): void
    {
        config()->set('recruitment.malware_scan.enabled', false);
        config()->set('recruitment.malware_scan.fail_closed', true);

        $file = UploadedFile::fake()->create('candidate.pdf', 10, 'application/pdf');

        $this->assertFalse(app(MalwareScanner::class)->scan($file));
    }

    public function test_every_report_route_has_a_permission_middleware(): void
    {
        $routeNames = [
            'report-data-pelamar.index',
            'report-data-pelamar.export',
            'report-hasil-test-zoom.index',
            'report-hasil-test-zoom.export',
            'report-hasil-test-mmpi.index',
            'report-hasil-test-mmpi.export',
            'report-interview-kandidat.index',
            'report-interview-kandidat.export',
            'report.offering-letter.index',
            'report.offering-letter.export',
            'report.interviewer.index',
            'report.interviewer.export',
        ];

        foreach ($routeNames as $name) {
            $middleware = app('router')->getRoutes()->getByName($name)?->gatherMiddleware() ?? [];
            $this->assertNotEmpty(
                array_filter($middleware, fn (string $item) => str_starts_with($item, 'permission:')),
                "Route {$name} tidak memiliki permission middleware."
            );
        }
    }

    public function test_recruitment_audit_routes_are_permission_protected(): void
    {
        foreach ([
            'admin.recruitment-audits.index',
            'admin.recruitment-audits.show',
            'admin.recruitment-audits.export',
        ] as $name) {
            $middleware = app('router')->getRoutes()->getByName($name)?->gatherMiddleware() ?? [];
            $this->assertNotEmpty(array_filter(
                $middleware,
                fn (string $item) => str_starts_with($item, 'permission:')
            ));
        }
    }

    public function test_spreadsheet_formula_values_are_neutralized(): void
    {
        $safe = app(SpreadsheetValueSanitizer::class);

        foreach (['=1+1', '+cmd', '-10+20', '@SUM(A1:A2)'] as $value) {
            $this->assertStringStartsWith("'", $safe->sanitize($value));
        }

        $this->assertSame('Normal value', $safe->sanitize('Normal value'));
    }
}
