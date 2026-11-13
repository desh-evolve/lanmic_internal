<?php

return [
    'base_url' => env('SAGE300_BASE_URL'),
    'username' => env('SAGE300_USERNAME'),
    'password' => env('SAGE300_PASSWORD'),
    'timeout' => (int) env('SAGE300_TIMEOUT', 30),
];
