<?php

namespace Tests\Feature\Security;

use App\Jobs\SyncInterviewCalendar;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Tests\TestCase;

class TlsAndQueuedIntegrationTest extends TestCase
{
    public function test_production_code_does_not_disable_tls_verification(): void
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(app_path())
        );

        foreach ($files as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $path = $file->getPathname();
            $contents = file_get_contents($path);
            $this->assertStringNotContainsString('withoutVerifying', $contents, $path);
            $this->assertStringNotContainsString("'verify' => false", $contents, $path);
        }

        $this->assertTrue((bool) config('services.http.verify_tls'));
    }

    public function test_calendar_integration_is_a_retryable_unique_job(): void
    {
        $calendar = new SyncInterviewCalendar('schedule-id', 'sync', 'calendar-key');

        $this->assertInstanceOf(ShouldQueue::class, $calendar);
        $this->assertInstanceOf(ShouldBeUnique::class, $calendar);
        $this->assertSame(3, $calendar->tries);
    }
}
