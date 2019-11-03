<?php

namespace Grananda\AwsFaceMatch\Services;

use Aws\Result;
use Illuminate\Support\Facades\Log;
use Aws\Rekognition\Exception\RekognitionException;

final class AwsFaceMatchCollectionService extends AwsFaceMatchService
{
    /**
     * Initializes a collection when not available.
     *
     * @param string $collection
     *
     * @return \Aws\Result|bool
     */
    public function initializeCollection(string $collection)
    {
        if (! $this->collectionExists($collection)) {
            return $this->client->createCollection(
                [
                    'CollectionId' => $collection,
                ]
            );
        }

        return true;
    }

    /**
     * Determines if a specific collection already exists for current AWS credentials.
     *
     * @param string $collection
     *
     * @return bool
     */
    private function collectionExists(string $collection)
    {
        /** @var Result $collections */
        $collections = $this->client->listCollections();

        return in_array($collection, $collections->toArray()['CollectionIds']);
    }

    public function purgeCollections()
    {
        /** @var Result $collections */
        $collections = $this->client->listCollections();

        /** @var array $collectionIds */
        $collectionIds = $collections->toArray()['CollectionIds'];

        foreach ($collectionIds as $collectionId) {
            $this->deleteCollection($collectionId);
        }

        return (bool) sizeof($collectionIds);
    }

    /**
     * Removes a single collection.
     *
     * @param string $collection
     *
     * @return bool|Result
     */
    public function deleteCollection(string $collection)
    {
        try {
            return $this->client->deleteCollection(
                [
                    'CollectionId' => $collection,
                ]
            );
        } catch (RekognitionException $rekognitionException) {
            Log::info($rekognitionException->getMessage());

            return false;
        }
    }

    /**
     * Returns details for given collection.
     *
     * @param string $collection
     *
     * @return bool|Result
     */
    public function describeCollection(string $collection)
    {
        try {
            return $this->client->describeCollection(
                [
                    'CollectionId' => $collection,
                ]
            );
        } catch (RekognitionException $rekognitionException) {
            Log::info($rekognitionException->getMessage());

            return false;
        }
    }
}
