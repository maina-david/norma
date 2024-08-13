<?php

namespace App\Http\Controllers\Api\Internals\My\Corpus;

use App\Http\Resources\Internals\My\Corpus\ReferenceContentExtractResource;
use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceContentExtract;
use App\Models\Tasks\Task;
use App\Services\Customer\ActiveLibryosManager;
use App\Services\Tasks\AITaskGenerator;
use App\Traits\UpdatesReferenceContentExtract;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReferenceExtractController
{
    use UpdatesReferenceContentExtract;

    /**
     * @param \App\Services\Customer\ActiveLibryosManager $manager
     * @param \App\Models\Corpus\Reference                $reference
     * @param AITaskGenerator                             $generator
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(ActiveLibryosManager $manager, Reference $reference, AITaskGenerator $generator): AnonymousResourceCollection
    {
        if (!ReferenceContentExtract::where('reference_id', $reference->id)->exists()) {
            $this->updateFromGPT($reference, $generator);
        }

        $content = ReferenceContentExtract::where('reference_id', $reference->id)
            ->select(['id', 'content'])
            ->addSelect([
                'attached' => Task::whereColumn('reference_content_extract_id', qualify_column(ReferenceContentExtract::class, 'id'))
                    ->forLibryoOrOrganisation($manager->getActive(), $manager->getActiveOrganisation())
                    ->selectRaw('1'),
            ])
            ->withCasts([
                'attached' => 'boolean',
            ])
            ->get(['id', 'content']);

        return ReferenceContentExtractResource::collection($content);
    }
}
