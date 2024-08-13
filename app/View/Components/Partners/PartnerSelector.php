<?php

namespace App\View\Components\Partners;

use App\Models\Partners\Partner;
use Closure;
use Illuminate\View\Component;

/**
 * @todo remove if not ever used
 *
 * @codeCoverageIgnore
 */
class PartnerSelector extends Component
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
        Partner::all()->map(function ($p) use (&$options) {
            $options[$p->id] = $p->title;
        });

        /** @var \Illuminate\Contracts\View\View */
        $view = view('components.partners.partner-selector', [
            'options' => $options,
        ]);

        return $view;
    }
}
