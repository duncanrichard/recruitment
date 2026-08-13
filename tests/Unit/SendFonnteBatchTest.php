<?php

namespace Tests\Unit;

use App\Jobs\SendFonnteBatch;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use PHPUnit\Framework\TestCase;

class SendFonnteBatchTest extends TestCase
{
    public function test_fonnte_delivery_is_queued_retryable_and_idempotent(): void
    {
        $job = new SendFonnteBatch('company-id', [], 'idempotency-key');

        $this->assertInstanceOf(ShouldQueue::class, $job);
        $this->assertInstanceOf(ShouldBeUnique::class, $job);
        $this->assertSame('idempotency-key', $job->uniqueId());
        $this->assertSame(3, $job->tries);
        $this->assertSame([30, 120, 300], $job->backoff);
    }
}
