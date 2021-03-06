<?php

namespace Grananda\AwsFaceMatch\Jobs;

use Aws\Result;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Grananda\AwsFaceMatch\Models\Collection;
use Grananda\AwsFaceMatch\Models\FaceMatchEntity;
use Grananda\AwsFaceMatch\Services\AwsFaceMatchFaceService;
use Grananda\AwsFaceMatch\Services\AwsFaceMatchCollectionService;

class StoreEntityFaceImage implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Name of the collection to store the image data.
     *
     * @var string
     */
    private $collection;

    /**
     * The face recognition entity unique id.
     *
     * @var string
     */
    private $subjectId;

    /**
     * Image path for the entity to store.
     *
     * @var string
     */
    private $file;

    /**
     * Defines if model field contains binary data.
     *
     * @var bool
     */
    private $binary = false;

    /**
     * Entity class type.
     *
     * @var string
     */
    private $entity;

    /**
     * EntityImageWasStored constructor.
     *
     * @param string $collection
     * @param string $subjectId
     * @param string $file
     * @param string $entity
     * @param bool   $binary
     */
    public function __construct(
        string $collection,
        string $subjectId,
        string $file,
        string $entity,
        bool $binary = false
    ) {
        $this->collection = $collection;
        $this->subjectId  = $subjectId;
        $this->file       = $file;
        $this->entity     = $entity;
        $this->binary     = $binary;
    }

    /**
     * Execute the job.
     *
     * @param AwsFaceMatchCollectionService $awsFaceMatchCollectionService
     * @param AwsFaceMatchFaceService       $awsFaceMatchFaceService
     *
     * @return void
     */
    public function handle(
        AwsFaceMatchCollectionService $awsFaceMatchCollectionService,
        AwsFaceMatchFaceService $awsFaceMatchFaceService
    ) {
        /** @var bool|Result $collection */
        $collectionResponse = $awsFaceMatchCollectionService->initializeCollection($this->collection);

        $collection = $this->findOrCreateCollection($collectionResponse);

        /** @var false|Result $response */
        $response = $awsFaceMatchFaceService->indexFace($this->collection, $this->subjectId, $this->file, $this->binary);

        if ($response) {
            /** @var array $item */
            $item = $response->get('FaceRecords')[0];

            FaceMatchEntity::updateOrCreate(
                [
                    'collection_id' => $collection->id,
                    'entity_ref'    => $this->subjectId,
                ],
                [
                    'collection_id' => $collection->id,
                    'face_id'       => $item['Face']['FaceId'],
                    'image_id'      => $item['Face']['ImageId'],
                    'entity_ref'    => $this->subjectId,
                ]
            );
        }
    }

    /**
     * Retrieves or create a Face Rekognition collection in local database.
     *
     * @param Result $collectionResponse
     *
     * @return Collection|null
     */
    private function findOrCreateCollection($collectionResponse)
    {
        $collection = null;

        if (! $collectionResponse) {
            $collection = Collection::where('collection_id', $this->collection)->first();
        }

        if (! $collectionResponse || ! $collection) {
            $collection = Collection::updateOrCreate(
                [
                    'collection_arn' => $collectionResponse->get('CollectionArn') ?? $collectionResponse->get('CollectionARN'),
                ],
                [
                    'collection_arn' => $collectionResponse->get('CollectionArn') ?? $collectionResponse->get('CollectionARN'),
                    'collection_id'  => $this->collection,
                    'entity'         => $this->entity,
                ]
            );
        }

        return $collection;
    }
}
