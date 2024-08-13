<?php

namespace App\View\Components\Partners;

use App\Models\Partners\WhiteLabel;
use Closure;
use Illuminate\View\Component;

class WhiteLabelSelector extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(public ?int $value = null, public ?string $label = null, public bool $required = false)
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|Closure|string
     */
    public function render()
    {
        $options = ['' => '--'];
        WhiteLabel::all()->map(function ($w) use (&$options) {
            $options[$w->id] = $w->title;
        });

        /** @var \Illuminate\Contracts\View\View */
        $view = view('components.partners.white-label-selector', [
            'options' => $options,
        ]);

        return $view;
    }
}
