<?php

namespace Grananda\AwsFaceMatch\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Grananda\AwsFaceMatch\Models\Collection;
use Grananda\AwsFaceMatch\Models\FaceMatchEntity;
use Grananda\AwsFaceMatch\Services\AwsFaceMatchFaceService;

class RemoveEntityFaceImage implements ShouldQueue
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
     * The entity identifier to remove from DB.
     *
     * @var string
     */
    private $identifier;

    /**
     * EntityImageWasStored constructor.
     *
     * @param string $collection
     * @param string $identifier
     */
    public function __construct(
        string $collection,
        string $identifier
    ) {
        $this->collection = $collection;
        $this->identifier = $identifier;
    }

    /**
     * Execute the job.
     *
     * @param AwsFaceMatchFaceService $awsFaceMatchFaceService
     *
     * @throws \Exception
     *
     * @return void
     */
    public function handle(AwsFaceMatchFaceService $awsFaceMatchFaceService)
    {
        /** @var Collection $collection */
        $collection = Collection::where('collection_id', $this->collection)->firstOrFail();

        /** @var FaceMatchEntity $entity */
        $entity = $collection->faces()->where('entity_ref', $this->identifier)->firstOrFail();

        $awsFaceMatchFaceService->forgetFaces($this->collection, [$entity->face_id]);

        $entity->delete();
    }
}
