<?php

namespace App\View\Components\Collaborators\Collaborate;

use App\Models\Collaborators\ProductionPod;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ProductionPodSelector extends Component
{
    /** @var array<int, mixed> */
    public array $pods;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render()
    {
        $this->pods = ['' => '-'] + ProductionPod::orderBy('title')->pluck('title', 'id')->toArray();

        /** @var View */
        return view('components.collaborators.collaborate.production-pod-selector');
    }
}
