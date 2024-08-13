<?php

namespace App\View\Components\Corpus\Work;

use App\Models\Corpus\Work;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class WorkPanel extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        public Work $work,
        public bool $showFlag = true,
        public bool $showType = false,
        public bool $showMultipleFlags = false,
    ) {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|Closure|string
     */
    public function render()
    {
        /** @var View */
        return view('components.corpus.work.work-panel');
    }
}
