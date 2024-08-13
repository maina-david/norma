<?php

namespace App\View\Components\Lookups;

use App\Enums\Lookups\CannedResponseField;
use App\Models\Lookups\CannedResponse;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CannedResponseSelector extends Component
{
    /** @var array<string, string> */
    public array $options = [];

    public function __construct(
        protected string $field,
        public string $name = 'canned_response',
        public ?string $label = null,
        public ?string $value = null,
    ) {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render()
    {
        /** @var CannedResponseField */
        $field = CannedResponseField::fromValue($this->field);
        $this->options = ['' => '-'] + CannedResponse::byType($field)
            ->pluck('response', 'response')
            ->toArray();

        /** @var View */
        return view('components.lookups.canned-response-selector');
    }
}
