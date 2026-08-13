<?php

namespace App\Services;

use Illuminate\Database\Events\QueryExecuted;

class DatabaseMutationAuditParser
{
    public function metadata(QueryExecuted $query): array
    {
        preg_match('/^\s*(insert|update|delete)\b/i', $query->sql, $operation);
        preg_match('/(?:into|update|from)\s+["`]?([a-zA-Z0-9_]+)["`]?/i', $query->sql, $table);

        $changedColumns = [];
        if (preg_match('/\bset\s+(.+?)\s+where\b/is', $query->sql, $set)) {
            preg_match_all('/["`]?([a-zA-Z0-9_]+)["`]?\s*=\s*\?/i', $set[1], $columns);
            $changedColumns = $columns[1] ?? [];
        }

        $whereSql = preg_match('/\bwhere\b(.+)$/is', $query->sql, $where)
            ? $where[1]
            : '';
        preg_match_all('/["`]?([a-zA-Z0-9_]+)["`]?\s*(?:=|in)\s*\(?\?/i', $whereSql, $whereColumns);

        $newValues = [];
        foreach ($changedColumns as $index => $column) {
            $newValues[$column] = $this->safeValue($column, $query->bindings[$index] ?? null);
        }

        $recordIdentifiers = [];
        $whereOffset = count($changedColumns);
        foreach (($whereColumns[1] ?? []) as $index => $column) {
            if ($column === 'id' || $column === 'uuid' || str_ends_with($column, '_id')) {
                $recordIdentifiers[$column] = $this->safeValue(
                    $column,
                    $query->bindings[$whereOffset + $index] ?? null
                );
            }
        }

        return [
            'connection' => $query->connectionName,
            'operation' => strtolower($operation[1] ?? 'mutation'),
            'table' => $table[1] ?? null,
            'changed_columns' => array_values(array_unique($changedColumns)),
            'where_columns' => array_values(array_unique($whereColumns[1] ?? [])),
            'new_values' => $newValues,
            'record_identifiers' => $recordIdentifiers,
            'bindings_count' => count($query->bindings),
            'bindings_fingerprint' => hash('sha256', json_encode($query->bindings)),
            'sql_fingerprint' => hash('sha256', $query->sql),
            'duration_ms' => $query->time,
        ];
    }

    private function safeValue(string $column, mixed $value): mixed
    {
        if (preg_match('/password|token|secret|api[_-]?key|ciphertext/i', $column)) {
            return '[REDACTED]';
        }

        if (is_scalar($value) || $value === null) {
            return is_string($value) ? mb_substr($value, 0, 1000) : $value;
        }

        return '[COMPLEX_VALUE]';
    }
}
