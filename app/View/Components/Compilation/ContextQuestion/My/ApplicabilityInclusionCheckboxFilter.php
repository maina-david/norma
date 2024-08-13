<?php

namespace App\View\Components\Compilation\ContextQuestion\My;

use App\Enums\Compilation\ApplicabilityInclusion;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ApplicabilityInclusionCheckboxFilter extends Component
{
    /**
     * @param array<string, mixed> $applied
     */
    public function __construct(public array $applied = [])
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View
     */
    public function render(): View
    {
        /** @var View */
        return view('components.compilation.context-question.my.applicability-inclusion-checkbox-filter', [
            'included' => ApplicabilityInclusion::INCLUDED_IN_STREAM,
            'excluded' => ApplicabilityInclusion::EXCLUDED_FROM_STREAM,
        ]);
    }
}
