<?php

namespace App\Http\Controllers\Api\Internals\My\Compilations;

use App\Http\Controllers\Controller;
use App\Http\Resources\Internals\Collaborate\Compilation\ContextQuestionResource;
use App\Http\Resources\Internals\My\Customer\LibryoResource;
use App\Http\Resources\Ontology\LegalDomain\V1\LegalDomainResource;
use App\Services\Customer\ActiveLibryosManager;
use App\Traits\Corpus\UsesReferenceApplicability;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ApplicabilityListingController extends Controller
{
    use UsesReferenceApplicability;

    /**
     * Returns Linked action areas in advanced applicability section.
     *
     * @param int $reference
     *
     * @return JsonResponse
     */
    public function index(int $reference): JsonResponse
    {
        $manager = app(ActiveLibryosManager::class);

        $referenceData = $this->getAppliedApplicability($reference, $manager);

        /** @var \App\Models\Auth\User $user */
        $user = Auth::user();
        $canManageApplicability = $user->canManageApplicability();

        return response()->json([
            'data' => [
                'libryo' => new LibryoResource($referenceData['libryo']),
                'locations' => $referenceData['locations'],
                'questions' => ContextQuestionResource::collection($referenceData['questions']),
                'categories' => LegalDomainResource::collection($referenceData['categories']),
                'canManageApplicability' => $canManageApplicability,
            ],
        ]);
    }
}
