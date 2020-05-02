<?php

return [
    'default'     => env('QUEUE_CONNECTION'),
    'connections' => [
        'sync'       => [
            'driver' => 'sync',
        ],
        'database'   => [
            'driver'      => 'database',
            'table'       => 'jobs',
            'queue'       => 'default',
            'retry_after' => 90,
        ],
        'beanstalkd' => [
            'driver'      => 'beanstalkd',
            'host'        => 'localhost',
            'queue'       => 'default',
            'retry_after' => 90,
            'block_for'   => 0,
        ],
        'sqs'        => [
            'driver' => 'sqs',
            'key'    => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'prefix' => env('SQS_PREFIX'),
            'queue'  => env('SQS_QUEUE'),
            'region' => env('AWS_DEFAULT_REGION'),
        ],
        'redis'      => [
            'driver'      => 'redis',
            'connection'  => 'default',
            'queue'       => env('REDIS_QUEUE'),
            'retry_after' => 90,
            'block_for'   => 0,
        ],
    ],
    'failed'      => [
        'driver'   => env('QUEUE_FAILED_DRIVER'),
        'database' => env('DB_CONNECTION'),
        'table'    => 'failed_jobs',
    ]
];
