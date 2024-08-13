<?php

namespace App\View\Components\Ui\My;

use App\Models\Customer\Organisation;
use App\Services\Customer\ActiveLibryosManager;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class GlobalSearch extends Component
{
    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render()
    {
        $manager = app(ActiveLibryosManager::class);
        /** @var Organisation */
        $organisation = $manager->getActiveOrganisation();

        $searchItems = [];

        $searchItems[] = [
            'heading' => __('corpus.work.requirements'),
            'icon' => 'gavel',
            'link' => route('my.corpus.requirements.index'),
            'text' => __('corpus.reference.search_requirements_for'),
        ];
        $searchItems[] = [
            'heading' => __('my.nav.updates'),
            'icon' => 'bell',
            'link' => route('my.notify.legal-updates.index'),
            'text' => __('notify.legal_update.search_updates_for'),
        ];
        $searchItems[] = [
            'heading' => __('my.nav.drives'),
            'icon' => 'folder-open',
            'link' => route('my.drives.files.index'),
            'text' => __('storage.drives.search_drives_for'),
        ];

        if ($organisation->hasAssessModule()) {
            $searchItems[] = [
                'heading' => __('my.nav.assess'),
                'icon' => 'clipboard-list',
                'link' => route('my.assess.assessment-item-responses.index'),
                'text' => __('assess.assessment_item.search_assess_for'),
            ];
        }
        if ($organisation->hasTaskModule()) {
            $searchItems[] = [
                'heading' => __('my.nav.tasks'),
                'icon' => 'tasks',
                'link' => route('my.tasks.tasks.index'),
                'text' => __('tasks.task.search_tasks_for'),
            ];
        }

        /** @var View */
        return view('components.ui.my.global-search', [
            'searchItems' => $searchItems,
        ]);
    }
}
