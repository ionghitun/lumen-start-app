<?php

return [
    'default' => env('BROADCAST_DRIVER'),
    'connections' => [
        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
        ],
        'log' => [
            'driver' => 'log',
        ],
        'null' => [
            'driver' => 'null',
        ]
    ]
];
