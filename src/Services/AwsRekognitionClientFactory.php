<?php

namespace Grananda\AwsFaceMatch\Services;

use Aws\Rekognition\RekognitionClient;

/**
 * Class AwsRekognitionClientFactory.
 *
 * @package Grananda\AwsFaceMatch\Services
 */
class AwsRekognitionClientFactory
{
    /**
     * Initializes instance of RekognitionClient.
     *
     * @return RekognitionClient
     */
    public static function instantiate()
    {
        /** @var array $options */
        $options = [
            'region'      => config('facematch.aws.region'),
            'version'     => config('facematch.aws.version'),
            'credentials' => [
                'key'    => config('facematch.aws.key'),
                'secret' => config('facematch.aws.secret'),
            ],
        ];

        return new RekognitionClient($options);
    }
}
