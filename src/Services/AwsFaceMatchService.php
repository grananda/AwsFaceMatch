<?php

namespace Grananda\AwsFaceMatch\Services;

use Aws\Rekognition\RekognitionClient;

/**
 * Class AwsFaceMatchService.
 *
 * @package Grananda\AwsFaceMatch\Services
 */
abstract class AwsFaceMatchService
{
    const IMAGE_INDEXING_RESPONSE_ATTRIBUTE = ['DEFAULT'];

    const IMAGE_FILTER_PROCESSING_LEVEL = 'AUTO';

    const MAXIMUM_FACES_TO_PROCESS = 1;

    /**
     * The AWS Rekognition client instance.
     *
     * @var RekognitionClient
     */
    protected $client;

    /**
     * AwsFaceMatchService constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->client = AwsRekognitionClientFactory::instantiate();
    }
}
