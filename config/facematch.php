<?php

use Grananda\AwsFaceMatch\Tests\Models\Entity;
use Grananda\AwsFaceMatch\Tests\Models\BinEntity;
use Grananda\AwsFaceMatch\Tests\Models\OtherEntity;

return [
    /*
     * Configure the AWS credentials.
     *
     * The mandatory parameters to include into the .env file are key, secret.
     * Region and version parameter is only necessary if different than default.
     */

    'aws' => [
        'key'     => env('AWS_ACCESS_KEY_ID'),
        'secret'  => env('AWS_SECRET_ACCESS_KEY'),
        'region'  => env('AWS_REGION', 'eu-central-1'),
        'version' => env('AWS_REKOGNITION_VERSION', 'latest'),
    ],

    /*
     * Configure the models that for which we want to use the facial recognition feature.
     *
     * Add a new array item per model. Each array element uses model class as its key.
     * Each model must contain the following elements:
     *  - Collection: wherein AWS will the avatar images and user reference be indexed.
     *    If none, a combination of the model namespace and className will be taken as the default collection.
     *  - Media Field: determines which field in the model database will the avatar image URL be stored.
     *  - Identifier: which unique field in the model database will be used to identify the record once a face match occurs.
     *    It is recommended to use a UUID field for such a purpose.
     */

    'recognize' => [
        Entity::class => [
            'collection' => 'entity',
            'identifier' => 'uuid',
            'media'      => [
                'field'  => 'media_url',
                'binary' => false,
            ],
        ],
        BinEntity::class => [
            'collection' => 'entity',
            'identifier' => 'uuid',
            'media'      => [
                'field'  => 'media_url',
                'binary' => true,
            ],
        ],
        OtherEntity::class => [
            'identifier' => 'uuid',
            'media'      => [
                'field'  => 'media_url',
                'binary' => false,
            ],
        ],
    ],
];
