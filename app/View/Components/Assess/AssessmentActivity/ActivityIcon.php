<?php

namespace App\View\Components\Assess\AssessmentActivity;

use App\Enums\Assess\AssessActivityType;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ActivityIcon extends Component
{
    /** @var string */
    public string $icon = 'exchange';

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(public int $activityType)
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render()
    {
        $this->icon = match ($this->activityType) {
            AssessActivityType::answerChange()->value => 'exchange',
            AssessActivityType::responseAdded()->value => 'exchange',
            AssessActivityType::comment()->value => 'comment-lines',
            AssessActivityType::fileUpload()->value => 'cloud-upload',
            // @codeCoverageIgnoreStart
            default => 'exchange',
            // @codeCoverageIgnoreEnd
        };

        /** @var View */
        return view('components.assess.assessment-activity.activity-icon');
    }
}
