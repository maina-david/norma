<?php

namespace App\Traits\Corpus;

use App\Enums\Compilation\ContextQuestionAnswer;
use App\Models\Corpus\Reference;
use App\Models\Customer\Norma;
use App\Models\Customer\Pivots\ContextQuestionNorma;
use App\Models\Geonames\Location;
use App\Models\Geonames\Pivots\LocationLocation;
use App\Services\Customer\ActiveNormasManager;

trait UsesReferenceApplicability
{
    /**
     * Get the common applicability fields.
     *
     * @param int                  $referenceId
     * @param ActiveNormasManager $manager
     *
     * @return array<string, mixed>
     */
    public function getAppliedApplicability(int $referenceId, ActiveNormasManager $manager): array
    {
        /** @var Reference $reference */
        $reference = Reference::findOrFail($referenceId);

        /** @var Norma $norma */
        $norma = $manager->getActive();

        $locations = LocationLocation::where('descendant', $norma->location_id)
            ->orderBy('depth', 'desc')
            ->pluck('ancestor');

        $fetched = Location::whereIn('id', $locations->all())->pluck('title', 'id');
        $locations = $locations->map(fn ($ancestor) => $fetched->get($ancestor));

        $questions = $reference->contextQuestions()
            ->whereHas('normas', function ($builder) use ($norma) {
                $builder->where('id', $norma->id)
                    ->where((new ContextQuestionNorma())->qualifyColumn('answer'), ContextQuestionAnswer::yes()->value);
            })
            ->get();

        $categories = $reference->legalDomains()
            ->whereRelation('normas', 'id', $norma->id)
            ->get();

        return [
            'reference' => $reference,
            'norma' => $norma,
            'locations' => $locations,
            'questions' => $questions,
            'categories' => $categories,
        ];
    }
}
