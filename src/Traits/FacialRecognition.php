<?php

namespace Grananda\AwsFaceMatch\Traits;

use Illuminate\Database\Eloquent\Model;
use Grananda\AwsFaceMatch\Jobs\FindFaceMatch;
use Grananda\AwsFaceMatch\Jobs\StoreEntityFaceImage;

trait FacialRecognition
{
    public static function boot()
    {
        parent::boot();

        static::saved(function (self $model) {
            if ($model->isDirty([$model->recognizable()['mediaField']])) {
                StoreEntityFaceImage::dispatch(
                    $model->getCollection(),
                    $model->getIdentifier(),
                    $model->getMediaFile()
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
    public function getMediaFile()
    {
        return $this->{$this->recognizable()['mediaField']};
    }

    /**
     * Returns media identifier.
     *
     * @return mixed
     */
    public function getIdentifier()
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

        /** @var string $identifier */
        $identifier = $entity->recognizable()['identifier'];

        /** @var string $id */
        if ($id = FindFaceMatch::dispatchNow($entity->getCollection(), $file)) {
            return $entity::where($identifier, $id)->first();
        }

        return false;
    }
}
