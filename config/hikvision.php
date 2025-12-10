<?php

return [
    'protocol' => env('HIK_PROTOCOL', 'http'),
    'host'     => env('HIK_HOST', '192.168.22.21'),
    'port'     => (int) env('HIK_PORT', 80),
    'user'     => env('HIK_USER', 'admin'),
    'pass'     => env('HIK_PASS', 'Eureca2025'),
];
