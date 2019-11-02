<?php

namespace Grananda\AwsFaceMatch\Services;

use Aws\Result;
use Illuminate\Support\Facades\File;

final class AwsFaceMatchFaceService extends AwsFaceMatchService
{
    const FACE_MATCH_THRESHOLD = 80;

    const MAX_RETURNED_MATCHING_FACES = 5;

    /**
     * Stores binary image pattern data into a collection.
     *
     * @param string $collection
     * @param string $subjectId
     * @param string $file
     * @param bool   $binary
     *
     * @return \Aws\Result|bool
     */
    public function indexFace(string $collection, string $subjectId, string $file, $binary = false)
    {
        if ($this->hasSingleFace($file, $binary)) {
            /** @var string $file */
            $file = $this->readFile($file, $binary);

            return $this->client->indexFaces(
                [
                    'CollectionId'        => $collection,
                    'DetectionAttributes' => self::IMAGE_INDEXING_RESPONSE_ATTRIBUTE,
                    'ExternalImageId'     => $subjectId,
                    'Image'               => [
                        'Bytes' => $file,
                    ],
                    'MaxFaces'      => self::MAXIMUM_FACES_TO_PROCESS,
                    'QualityFilter' => self::IMAGE_FILTER_PROCESSING_LEVEL,
                ]
            );
        }

        return false;
    }

    /**
     * Finds indexed matching face id from unknown image.
     *
     * @param string $collection
     * @param string $file
     *
     * @return Result
     */
    public function matchFace(string $collection, string $file)
    {
        /** @var string $file */
        $file = $this->readFile($file);

        return $this->client->searchFacesByImage(
            [
                'CollectionId'       => $collection,
                'FaceMatchThreshold' => self::FACE_MATCH_THRESHOLD,
                'Image'              => [
                    'Bytes' => $file,
                ],
                'MaxFaces' => self::MAX_RETURNED_MATCHING_FACES,
            ]
        );
    }

    /**
     * Detects if an image contains a single face.
     *
     * @param string $file
     * @param bool   $binary
     *
     * @return bool
     */
    private function hasSingleFace(string $file, bool $binary)
    {
        /** @var string $file */
        $file = $this->readFile($file, $binary);

        /** @var Result $faces */
        $faces = $this->client->detectFaces(
            [
                'Attributes' => self::IMAGE_INDEXING_RESPONSE_ATTRIBUTE,
                'Image'      => [
                    'Bytes' => $file,
                ],
            ]
        );

        return sizeof($faces->get('FaceDetails')) === 1 ? true : false;
    }

    /**
     * Reads image file content.
     *
     * @param string $file
     * @param bool   $binary
     *
     * @return false|string
     */
    private function readFile(string $file, $binary = false)
    {
        return $binary ? base64_decode($file) : file_get_contents($file);
    }
}
