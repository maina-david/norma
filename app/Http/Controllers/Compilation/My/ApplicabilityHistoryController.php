<?php

namespace App\Http\Controllers\Compilation\My;

use App\Models\Compilation\ApplicabilityActivity;
use App\Models\Customer\Libryo;
use App\Services\Customer\ActiveLibryosManager;
use Illuminate\View\View;

class ApplicabilityHistoryController
{
    /**
     * Get the activity listing.
     *
     * @param \App\Services\Customer\ActiveLibryosManager $manager
     *
     * @return \Illuminate\View\View
     */
    public function index(ActiveLibryosManager $manager): View
    {
        $libryo = $manager->getActive();
        $organisation = $manager->getActiveOrganisation();

        $libryoFilter = $libryo ? [$libryo->id] : Libryo::where('organisation_id', $organisation->id)->select(['id']);

        $activities = ApplicabilityActivity::whereIn('place_id', $libryoFilter)
            ->orderBy('id', 'desc')
            ->with([
                'contextQuestion', 'libryo', 'note', 'user',
                'reference:id', 'reference.refPlainText:reference_id,plain_text',
            ])
            ->paginate(20);

        /** @var View */
        return view('pages.compilation.my.context-question.applicability-history', ['activities' => $activities]);
    }
}
