<?php

namespace Tests\Feature\Security;

use Tests\TestCase;

class ReportTokenExposureTest extends TestCase
{
    public function test_candidate_access_tokens_are_not_exposed_by_report_controllers(): void
    {
        foreach (glob(app_path('Http/Controllers/Report/*.php')) as $file) {
            $contents = file_get_contents($file);

            $this->assertStringNotContainsString('drd.token', $contents, $file);
            $this->assertStringNotContainsString("'<th>Token</th>'", $contents, $file);
            $this->assertStringNotContainsString("'token' => \$item->token", $contents, $file);
        }
    }
}
