<?php

return [
    'driver'     => env('MAIL_DRIVER'),
    'host'       => env('MAIL_HOST'),
    'port'       => env('MAIL_PORT'),
    'from'       => [
        'address' => env('MAIL_FROM_ADDRESS'),
        'name'    => env('MAIL_FROM_NAME'),
    ],
    'encryption' => env('MAIL_ENCRYPTION'),
    'username'   => env('MAIL_USERNAME'),
    'password'   => env('MAIL_PASSWORD'),
    'sendmail'   => '/usr/sbin/sendmail -bs',
    'markdown'   => [
        'theme' => 'default',

        'paths' => [
            resource_path('views/vendor/mail'),
        ],
    ],
    'stream'     => [
        'ssl' => [
            'allow_self_signed' => true,
            'verify_peer'       => false,
            'verify_peer_name'  => false,
        ],
    ]
];
