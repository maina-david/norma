<?php

namespace App\Http\Controllers\Actions\My;

use App\Models\Actions\ActionArea;
use App\Services\Customer\ActiveLibryosManager;
use App\Traits\Actions\UsesActionAreasInLibryo;
use App\Traits\UsesBackButton;
use App\Traits\UsesReferencesForLibryo;
use Inertia\Inertia;
use Inertia\Response;

class ActionAreaPlannerController
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
     * Render the page.
     *
     * @return \Inertia\Response
     */
    public function subjects(): Response
    {
        return $this->routeToPage('topics');
    }

    /**
     * Render the page.
     *
     * @return \Inertia\Response
     */
    public function controls(): Response
    {
        return $this->routeToPage('controls');
    }

    /**
     * View the listing of actions.
     *
     * @param string $type
     *
     * @return \Inertia\Response
     */
    protected function routeToPage(string $type): Response
    {
        $manager = app(ActiveLibryosManager::class);

        $this->redirectIfNoActionAreas($manager->getActive());

        return Inertia::render('Actions/My/ActionArea/CategoryPlannerPage', [
            'active' => $type,
            'title' => null,
            'completion' => $this->getActionAreasWithTasksCount($manager),
        ]);
    }

    /**
     * Get the completion counts.
     *
     * @param \App\Services\Customer\ActiveLibryosManager $manager
     *
     * @return array<string, int>
     */
    protected function getActionAreasWithTasksCount(ActiveLibryosManager $manager): array
    {
        $libryo = $manager->getActive();
        $organisation = $manager->getActiveOrganisation();

        $references = $this->getReferenceSubQuery($libryo, $organisation);

        $total = ActionArea::whereHas('references', fn ($query) => $query->whereIn('id', $references))->count();

        $completed = ActionArea::whereHas('references', fn ($query) => $query->whereIn('id', $references))
            ->whereHas('tasks', fn ($query) => $query->forLibryoOrOrganisation($libryo, $organisation))
            ->count();

        return [
            'total' => $total,
            'completed' => $completed,
        ];
    }

    /**
     * Show the category view.
     *
     * @param \App\Models\Actions\ActionArea $action
     *
     * @return \Inertia\Response
     */
    public function showAction(ActionArea $action): Response
    {
        $action->load(['controlCategory.descriptions', 'subjectCategory.descriptions']);

        /** @var \Inertia\Response */
        return inertia('Actions/My/ActionArea/ShowPage', [
            'backButton' => $this->getPreviousUrl(route('my.actions.action-areas.subject.index')),
            'action' => [
                'id' => $action->id,
                'title' => $action->title,
                'control' => [
                    'display_label' => $action->controlCategory->display_label,
                    'description' => $action->controlCategory->descriptions->first()?->description,
                ],
                'subject' => [
                    'display_label' => $action->subjectCategory->display_label,
                    'description' => $action->subjectCategory->descriptions->first()?->description,
                ],
            ],
        ]);
    }
}
