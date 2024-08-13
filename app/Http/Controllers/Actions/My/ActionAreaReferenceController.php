<?php

namespace App\Http\Controllers\Actions\My;

use App\Models\Corpus\Reference;
use App\Services\Customer\ActiveLibryosManager;
use App\Traits\Actions\UsesActionAreasInLibryo;
use App\Traits\UsesBackButton;
use App\Traits\UsesReferencesForLibryo;
use Inertia\Inertia;
use Inertia\Response;

class ActionAreaReferenceController
{
    use UsesActionAreasInLibryo;
    use UsesBackButton;
    use UsesReferencesForLibryo;

    /**
     * @param \App\Services\Customer\ActiveLibryosManager $manager
     */
    public function __construct(protected ActiveLibryosManager $manager)
    {
        $this->redirectIfNoActionAreas($this->manager->getActive());
    }

    /**
     * @param \App\Services\Customer\ActiveLibryosManager $manager
     *
     * @return \Inertia\Response
     */
    public function index(ActiveLibryosManager $manager): Response
    {
        $libryo = $manager->getActive();
        $organisation = $manager->getActiveOrganisation();

        $references = $this->getReferenceSubQuery($libryo, $organisation);

        $query = Reference::whereIn('id', $references)
            ->forActiveWork()
            ->active()
            ->has('actionAreas');

        $total = $query->clone()->count();

        $completed = $query->whereHas('tasks', fn ($query) => $query->forLibryoOrOrganisation($libryo, $organisation))
            ->count();

        return Inertia::render('Actions/My/ActionArea/RequirementPlannerPage', [
            'active' => 'requirements',
            'title' => __('requirements.requirements'),
            'completion' => [
                'total' => $total,
                'completed' => $completed,
            ],
        ]);
    }

    /**
     * @param \App\Models\Corpus\Reference $reference
     *
     * @return \Inertia\Response
     */
    public function show(Reference $reference): Response
    {
        $reference->load(['refPlainText', 'actionAreas:id,title']);

        /** @var \Inertia\Response */
        return inertia('Actions/My/ActionArea/RequirementsShowPage', [
            'backButton' => $this->getPreviousUrl(route('my.actions.action-areas.requirements.index')),
            'reference' => [
                'id' => $reference->id,
                'title' => $reference->refPlainText->plain_text ?? '',
                'actionAreas' => $reference->actionAreas->all(),
            ],
        ]);
    }
}
