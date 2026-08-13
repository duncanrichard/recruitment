<?php

namespace App\Services;

class SpreadsheetValueSanitizer
{
    public function sanitizeRows(iterable $rows)
    {
        return collect($rows)->map(function ($row) {
            $values = is_object($row) ? get_object_vars($row) : $row;

            return collect($values)->map(fn ($value) => is_scalar($value) || $value === null
                ? $this->sanitize($value)
                : $value)->all();
        });
    }

    public function sanitize(mixed $value): string
    {
        $value = (string) ($value ?? '');

        return preg_match('/^[=+\-@]/', $value) ? "'".$value : $value;
    }
}
