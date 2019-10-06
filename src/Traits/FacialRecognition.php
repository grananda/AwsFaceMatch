<?php

namespace Grananda\AwsFaceMatch\Traits;

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
                    $model->getCollection()
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
}
