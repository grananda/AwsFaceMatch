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
            if ($model->isDirty([$model->getMediaField()])) {
                StoreEntityFaceImage::dispatch(
                    $model->getCollection(),
                    $model->getIdentifierValue(),
                    $model->getMediaFieldValue(),
                    get_class($model),
                    $model->isBinary()
                );
            }
        });
    }

    /**
     * Returns face match collection.
     *
     * @return mixed
     */
    public function getCollection()
    {
        /** @var string $collection */
        $collection = config('facematch.recognize.'.$this->getModelConfigArrayKey().'.collection');

        return $collection ?? $this->generateDefaultCollection();
    }

    /**
     * Returns media to index.
     *
     * @return mixed
     */
    public function getMediaFieldValue()
    {
        return $this->isBinary() ? base64_encode($this->{$this->getMediaField()}) : $this->{$this->getMediaField()};
    }

    /**
     * Returns media identifier.
     *
     * @return mixed
     */
    public function getIdentifierValue()
    {
        return $this->{$this->getIdentifier()};
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
    public function getMediaField()
    {
        return config('facematch.recognize.'.$this->getModelConfigArrayKey().'.media.field');
    }

    /**
     * Returns media identifier.
     *
     * @return mixed
     */
    public function getIdentifier()
    {
        return config('facematch.recognize.'.$this->getModelConfigArrayKey().'.identifier');
    }

    /**
     * Returns media field type binary assertion.
     *
     * @return \Illuminate\Config\Repository|mixed
     */
    public function isBinary()
    {
        return (bool) config('facematch.recognize.'.$this->getModelConfigArrayKey().'.media.binary');
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

    public static function facesForget(array $faceIds)
    {
        /** @var string $class */
        $class = self::class;

        /** @var FacialRecognition $entity */
        $entity = new $class();

        /** @var Result $result */
        $result = FaceMatch::forgetFaces($entity->getCollection(), $faceIds);

        return $result->get('DeletedFaces');
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

    /**
     * Returns config model configuration.
     *
     * @return string
     */
    private function getModelConfigArrayKey()
    {
        return get_class($this);
    }
}
