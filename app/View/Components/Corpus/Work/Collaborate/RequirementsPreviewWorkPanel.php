<?php

namespace App\View\Components\Corpus\Work\Collaborate;

use App\Models\Corpus\Work;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class RequirementsPreviewWorkPanel extends Component
{
    /**
     * @param Work $work
     */
    public function __construct(public Work $work)
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render()
    {
        /** @var View */
        return view('components.corpus.work.collaborate.requirements-preview-work-panel', [
            'work' => $this->work,
        ]);
    }
}
