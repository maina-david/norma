<?php

namespace App\Traits\Corpus;

use App\Enums\Compilation\ContextQuestionAnswer;
use App\Models\Corpus\Reference;
use App\Models\Customer\Libryo;
use App\Models\Customer\Pivots\ContextQuestionLibryo;
use App\Models\Geonames\Location;
use App\Models\Geonames\Pivots\LocationLocation;
use App\Services\Customer\ActiveLibryosManager;

trait UsesReferenceApplicability
{
    /**
     * Get the common applicability fields.
     *
     * @param int                  $referenceId
     * @param ActiveLibryosManager $manager
     *
     * @return array<string, mixed>
     */
    public function getAppliedApplicability(int $referenceId, ActiveLibryosManager $manager): array
    {
        /** @var Reference $reference */
        $reference = Reference::findOrFail($referenceId);

        /** @var Libryo $libryo */
        $libryo = $manager->getActive();

        $locations = LocationLocation::where('descendant', $libryo->location_id)
            ->orderBy('depth', 'desc')
            ->pluck('ancestor');

        $fetched = Location::whereIn('id', $locations->all())->pluck('title', 'id');
        $locations = $locations->map(fn ($ancestor) => $fetched->get($ancestor));

        $questions = $reference->contextQuestions()
            ->whereHas('libryos', function ($builder) use ($libryo) {
                $builder->where('id', $libryo->id)
                    ->where((new ContextQuestionLibryo())->qualifyColumn('answer'), ContextQuestionAnswer::yes()->value);
            })
            ->get();

        $categories = $reference->legalDomains()
            ->whereRelation('libryos', 'id', $libryo->id)
            ->get();

        return [
            'reference' => $reference,
            'libryo' => $libryo,
            'locations' => $locations,
            'questions' => $questions,
            'categories' => $categories,
        ];
    }
}
