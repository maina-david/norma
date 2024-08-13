<?php

namespace App\View\Components\Corpus\Work;

use App\Enums\Application\ApplicationType;
use App\Models\Corpus\Work;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class WorkSelector extends Component
{
    /** @var string */
    public string $listRoute;

    public function __construct(public ApplicationType $appType, public string $name = 'work_id', public ?Work $work = null, public ?string $frame = null)
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render()
    {
        $this->listRoute = $this->appType->is(ApplicationType::my())
            ? route('my.settings.corpus.works.for.selector.index')
            // @codeCoverageIgnoreStart
            : route('collaborate.works.for.selector.index');
        // @codeCoverageIgnoreEnd

        /** @var View */
        return view('components.corpus.work.work-selector');
    }
}
