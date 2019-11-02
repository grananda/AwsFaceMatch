<?php

namespace Grananda\AwsFaceMatch\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
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
     * EntityImageWasStored constructor.
     *
     * @param string $collection
     * @param string $subjectId
     * @param string $file
     * @param bool   $binary
     */
    public function __construct(string $collection, string $subjectId, string $file, bool $binary = false)
    {
        $this->collection = $collection;
        $this->subjectId  = $subjectId;
        $this->file       = $file;
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
        $awsFaceMatchCollectionService->initializeCollection($this->collection);

        $awsFaceMatchFaceService->indexFace($this->collection, $this->subjectId, $this->file, $this->binary);
    }
}
