<?php

return [
    'malware_scan' => [
        'enabled' => env('MALWARE_SCAN_ENABLED', false),
        'fail_closed' => env('MALWARE_SCAN_FAIL_CLOSED', env('APP_ENV') === 'production'),
        'binary' => env('MALWARE_SCAN_BINARY', 'clamscan'),
        'timeout' => (int) env('MALWARE_SCAN_TIMEOUT', 30),
    ],
    'candidate_token_lifetime_days' => (int) env('CANDIDATE_TOKEN_LIFETIME_DAYS', 90),
];
