<?php

use App\User;

return [
    'facematch' => [
        'model'      => User::class,
        'identifier' => 'uuid',
        'media'      => 'image',
        'aws'        => [
        ],
    ],
];
