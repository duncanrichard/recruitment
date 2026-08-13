<?php

namespace Tests\Feature\Security;

use Tests\TestCase;

class PrivateDocumentStorageTest extends TestCase
{
    public function test_local_document_disk_uses_private_storage(): void
    {
        $this->assertSame(
            storage_path('app/private'),
            config('filesystems.disks.local.root')
        );

        $this->assertArrayNotHasKey('visibility', config('filesystems.disks.local'));
    }

    public function test_external_integration_configuration_is_centralized(): void
    {
        $this->assertArrayHasKey('credentials', config('services.google_calendar'));
        $this->assertArrayHasKey('calendar_id', config('services.google_calendar'));
        $this->assertArrayHasKey('timezone', config('services.google_calendar'));
    }
}
