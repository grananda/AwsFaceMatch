<?php

namespace Grananda\AwsFaceMatch\Traits;

use Aws\Result;
use Illuminate\Database\Eloquent\Model;
use Grananda\AwsFaceMatch\Facades\FaceMatch;
use Grananda\AwsFaceMatch\Facades\FaceCollection;
use Grananda\AwsFaceMatch\Jobs\StoreEntityFaceImage;

trait FacialRecognition
{
    public static function bootFacialRecognition()
    {
        static::saved(function (self $model) {
            if ($model->isDirty([$model->recognizable()['mediaField']])) {
                StoreEntityFaceImage::dispatch(
                    $model->getCollection(),
                    $model->getIdentifierValue(),
                    $model->getMediaFileValue()
                );
            }
        });
    }

    /**
     * Defines parameters for model facial match.
     *
     * @return array
     */
    public function recognizable()
    {
        return [
            'collection' => $this->generateDefaultCollection(),
            'mediaField' => 'media_url',
            'identifier' => 'id',
        ];
    }

    /**
     * Returns face match collection.
     *
     * @return mixed
     */
    public function getCollection()
    {
        return $this->recognizable()['collection'] ?? $this->generateDefaultCollection();
    }

    /**
     * Returns media to index.
     *
     * @return mixed
     */
    public function getMediaFileValue()
    {
        return $this->{$this->recognizable()['mediaField']};
    }

    /**
     * Returns media identifier.
     *
     * @return mixed
     */
    public function getIdentifierValue()
    {
        return $this->{$this->recognizable()['identifier']};
    }

    /**
     * Generates default collection name from class name.
     *
     * @return mixed
     */
    private function generateDefaultCollection()
    {
        return str_replace('\\', '-', get_class($this));
    }

    /**
     * Returns media to index.
     *
     * @return mixed
     */
    public function getMediaFile()
    {
        return $this->recognizable()['mediaField'];
    }

    /**
     * Returns media identifier.
     *
     * @return mixed
     */
    public function getIdentifier()
    {
        return $this->recognizable()['identifier'];
    }

    /**
     * Finds model object from a given face image.
     *
     * @param string $file
     *
     * @return bool|Model
     */
    public static function faceMatch(string $file)
    {
        /** @var string $class */
        $class = self::class;

        /** @var FacialRecognition $entity */
        $entity = new $class();

        /** @var Result $result */
        $result = FaceMatch::matchFace($entity->getCollection(), $file);

        if (sizeof($result->get('FaceMatches')) === 0) {
            return false;
        }

        /** @var string $identifier */
        $identifier = $result->get('FaceMatches')['0']['Face']['ExternalImageId'];

        return $entity::where($entity->getIdentifier(), $identifier)->first();
    }

    /**
     * Clears model entire collection.
     *
     * @return Result
     */
    public static function purgeCollection()
    {
        /** @var string $class */
        $class = self::class;

        /** @var FacialRecognition $entity */
        $entity = new $class();

        return FaceCollection::deleteCollection($entity->getCollection());
    }
}
