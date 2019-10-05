<?php

return [
    'model'       => 'Model\\User',
    'identifier'  => 'uuid',
    'media_field' => 'media_url',
    'aws'         => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_REGION'),
    ],
];
