<?php

namespace App\Http\Controllers\Compilation\My;

use App\Services\Customer\ActiveLibryosManager;
use App\Traits\Corpus\UsesReferenceApplicability;
use HotwiredLaravel\TurboLaravel\Http\PendingTurboStreamResponse;
use Illuminate\Http\Response;

class ApplicabilityReferenceController
{
    use UsesReferenceApplicability;

    /**
     * Show the applicability pane.
     *
     * @param int $reference
     *
     * @return \HotwiredLaravel\TurboLaravel\Http\PendingTurboStreamResponse|\Illuminate\Http\Response
     */
    public function show(int $reference): PendingTurboStreamResponse|Response
    {
        $manager = app(ActiveLibryosManager::class);

        if (!$manager->isSingleMode()) {
            /** @var Response */
            return response('');
        }

        $referenceData = $this->getAppliedApplicability($reference, $manager);
        $reference = $referenceData['reference'];

        return singleTurboStreamResponse("applicability-for-{$reference->id}")
            ->view('partials.corpus.reference.my.applicability-for-reference', [
                ...$referenceData,
                'reference' => $reference,
            ]);
    }
}
