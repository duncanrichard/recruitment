<?php

namespace Tests\Unit;

use App\Services\DatabaseMutationAuditParser;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\SQLiteConnection;
use PDO;
use PHPUnit\Framework\TestCase;

class DatabaseMutationAuditParserTest extends TestCase
{
    public function test_update_metadata_contains_changed_values_and_record_identifiers(): void
    {
        $connection = new SQLiteConnection(new PDO('sqlite::memory:'));
        $event = new QueryExecuted(
            'update "candidates" set "status" = ? where "id" = ?',
            ['accepted', 'candidate-secret-id'],
            3.4,
            $connection
        );

        $metadata = (new DatabaseMutationAuditParser)->metadata($event);

        $this->assertSame(['status'], $metadata['changed_columns']);
        $this->assertContains('id', $metadata['where_columns']);
        $this->assertSame(2, $metadata['bindings_count']);
        $this->assertSame(['status' => 'accepted'], $metadata['new_values']);
        $this->assertSame(['id' => 'candidate-secret-id'], $metadata['record_identifiers']);
    }
}
