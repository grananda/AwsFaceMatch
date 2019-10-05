<?php

namespace Grananda\AwsFaceMatch\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Grananda\AwsFaceMatch\Services\AwsFaceMatchService;

class EntityImageWasStored implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The face recognition entity.
     *
     * @var Model
     */
    private $entity;

    /**
     * EntityImageWasStored constructor.
     *
     * @param Model $entity
     */
    public function __construct(Model $entity)
    {
        $this->entity = $entity;
    }

    /**
     * Execute the job.
     *
     * @param AwsFaceMatchService $awsFaceMatchService
     *
     * @return void
     */
    public function handle(AwsFaceMatchService $awsFaceMatchService)
    {
    }
}
