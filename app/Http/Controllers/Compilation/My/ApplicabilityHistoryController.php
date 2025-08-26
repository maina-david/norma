<?php

namespace App\Http\Controllers\Compilation\My;

use App\Models\Compilation\ApplicabilityActivity;
use App\Models\Customer\Norma;
use App\Services\Customer\ActiveNormasManager;
use Illuminate\View\View;

class ApplicabilityHistoryController
{
    /**
     * Get the activity listing.
     *
     * @param \App\Services\Customer\ActiveNormasManager $manager
     *
     * @return \Illuminate\View\View
     */
    public function index(ActiveNormasManager $manager): View
    {
        $norma = $manager->getActive();
        $organisation = $manager->getActiveOrganisation();

        $normaFilter = $norma ? [$norma->id] : Norma::where('organisation_id', $organisation->id)->select(['id']);

        $activities = ApplicabilityActivity::whereIn('place_id', $normaFilter)
            ->orderBy('id', 'desc')
            ->with([
                'contextQuestion', 'norma', 'note', 'user',
                'reference:id', 'reference.refPlainText:reference_id,plain_text',
            ])
            ->paginate(20);

        /** @var View */
        return view('pages.compilation.my.context-question.applicability-history', ['activities' => $activities]);
    }
}
