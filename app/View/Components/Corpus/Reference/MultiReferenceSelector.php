<?php

namespace App\View\Components\Corpus\Reference;

use App\Enums\Application\ApplicationType;
use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class MultiReferenceSelector extends Component
{
    /** @var string */
    public string $listRoute;

    /**
     * Create a new component instance.
     *
     * @param ApplicationType             $appType
     * @param int                         $workId
     * @param string|null                 $name
     * @param bool                        $single
     * @param string|null                 $workFieldName
     * @param array<array-key, Reference> $references
     * @param array<array-key, string>    $routeParams
     */
    public function __construct(
        public ApplicationType $appType,
        public int $workId = 0,
        public ?string $name = null,
        public bool $single = false,
        public ?string $workFieldName = null,
        public array $references = [],
        public array $routeParams = []
    ) {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render()
    {
        $this->listRoute = $this->appType->is(ApplicationType::my())
            ? route('my.settings.corpus.reference.for.selector.index', ['work' => $this->workId, ...$this->routeParams])
            // @codeCoverageIgnoreStart
            : route('collaborate.corpus.reference.for.selector.index', ['work' => $this->workId, ...$this->routeParams]);
        // @codeCoverageIgnoreEnd

        /** @var View */
        return view('components.corpus.reference.multi-reference-selector', ['work' => $this->workId ? Work::find($this->workId) : null]);
    }
}
