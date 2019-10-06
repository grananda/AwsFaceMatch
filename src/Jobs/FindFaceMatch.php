<?php

namespace Grananda\AwsFaceMatch\Jobs;

use Aws\Result;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Grananda\AwsFaceMatch\Services\AwsFaceMatchFaceService;

class FindFaceMatch implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Image file to compare and math fins.
     *
     * @var string
     */
    private $file;

    /**
     * Collection where to search for an image match.
     *
     * @var string
     */
    private $collection;

    /**
     * FindFaceMatch constructor.
     *
     * @param string $collection
     * @param string $file
     */
    public function __construct(string $collection, string $file)
    {
        $this->collection = $collection;
        $this->file       = $file;
    }

    /**
     * Execute the job.
     *
     * @param AwsFaceMatchFaceService $awsFaceMatchFaceService
     *
     * @return bool|string
     */
    public function handle(AwsFaceMatchFaceService $awsFaceMatchFaceService)
    {
        /** @var Result $result */
        $result = $awsFaceMatchFaceService->matchFace($this->collection, $this->file);

        if (sizeof($result->get('FaceMatches')) === 0) {
            return false;
        }

        return $result->get('FaceMatches')['0']['Face']['ExternalImageId'];
    }
}
