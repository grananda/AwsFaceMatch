<?php

return [
    'aws' => [
        'key'     => env('AWS_ACCESS_KEY_ID'),
        'secret'  => env('AWS_SECRET_ACCESS_KEY'),
        'region'  => env('AWS_REGION', 'eu-central-1'),
        'version' => env('AWS_REKOGNITION_VERSION', 'latest'),
    ],
];
