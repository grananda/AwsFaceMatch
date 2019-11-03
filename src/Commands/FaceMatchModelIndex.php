<?php

namespace Grananda\AwsFaceMatch\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Grananda\AwsFaceMatch\Traits\FacialRecognition;
use Grananda\AwsFaceMatch\Services\AwsFaceMatchFaceService;
use Grananda\AwsFaceMatch\Services\AwsFaceMatchCollectionService;

class FaceMatchModelIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'facematch:index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index all existing records for related models.';

    /**
     * The AwsFaceMatchCollectionService instance.
     *
     * @var AwsFaceMatchCollectionService
     */
    private $awsFaceMatchCollectionService;

    /**
     * The AwsFaceMatchFaceService instance.
     *
     * @var AwsFaceMatchFaceService
     */
    private $awsFaceMatchFaceService;

    /**
     * Create a new command instance.
     *
     * @param AwsFaceMatchCollectionService $awsFaceMatchCollectionService
     * @param AwsFaceMatchFaceService       $awsFaceMatchFaceService
     */
    public function __construct(
        AwsFaceMatchCollectionService $awsFaceMatchCollectionService,
        AwsFaceMatchFaceService $awsFaceMatchFaceService
    ) {
        parent::__construct();

        $this->awsFaceMatchCollectionService = $awsFaceMatchCollectionService;
        $this->awsFaceMatchFaceService       = $awsFaceMatchFaceService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $models = config('facematch.recognize');

        foreach ($models as $key => $value) {
            /** @var FacialRecognition $entity */
            $model = new $key();

            /** @var Collection $records */
            if ($records = $model->get()) {
                /** @var string $collection */
                $collection = $model->getCollection();

                $this->awsFaceMatchCollectionService->initializeCollection($collection);

                foreach ($records as $record) {
                    /** @var bool $binary */
                    $binary = $record->isBinary();

                    $this->awsFaceMatchFaceService->indexFace($collection, $record->getIdentifierValue(),
                        $record->getMediaFieldValue(), $binary);
                }
            }
        }
    }
}
