<?php

namespace App\View\Components\Compilation\ContextQuestion\My;

use App\Enums\Compilation\ApplicabilityRecommendation;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ApplicabilityRecommendationCheckboxFilter extends Component
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
        return view('components.compilation.context-question.my.applicability-recommendation-checkbox-filter', [
            'recommended' => ApplicabilityRecommendation::RECOMMENDED,
        ]);
    }
}
