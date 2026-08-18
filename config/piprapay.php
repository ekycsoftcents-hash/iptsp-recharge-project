<?php

return [
    'base_url' => env('PIPRAPAY_BASE_URL'),
    'api_key' => env('PIPRAPAY_API_KEY'),
    'webhook_secret' => env('PIPRAPAY_WEBHOOK_SECRET'),
    'return_url' => env('PIPRAPAY_RETURN_URL'),
    'cancel_url' => env('PIPRAPAY_CANCEL_URL'),
    'timeout' => (int) env('PIPRAPAY_TIMEOUT', 20),
];
