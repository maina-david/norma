<?php

namespace App\View\Components\Workflows\Collaborate;

use App\Models\Workflows\Board;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\Component;

class TaskCreateWizardButton extends Component
{
    /** @var Collection<Board> */
    public Collection $boards;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(public ?int $projectId)
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render()
    {
        $this->boards = Board::orderBy('title')->whereNull('archived_at')->get(['id', 'title']);

        /** @var View */
        return view('components.workflows.collaborate.task-create-wizard-button');
    }
}
