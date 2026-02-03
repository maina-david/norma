<?php

namespace App\View\Components\Tasks\TaskProject;

use App\Models\Customer\Organisation;
use App\Services\Customer\ActiveNormasManager;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ProjectSelector extends Component
{
    /** @var array<int,string> */
    public array $projects = [];

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        public ?int $value,
        public string $name = 'project',
        public string $label = ''
    ) {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|Closure|string
     */
    public function render()
    {
        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);
        /** @var Organisation */
        $organisation = $manager->getActiveOrganisation();

        foreach ($organisation->taskProjects()->active()->get(['id', 'title']) as $project) {
            $this->projects[(int) $project->id] = $project->title;
        }

        /** @var View */
        return view('components.tasks.task-project.project-selector');
    }
}
