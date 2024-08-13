<?php

namespace App\View\Components\Collaborators;

use App\Models\Collaborators\Collaborator;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\Component;

class CollaboratorSelector extends Component
{
    /** @var array<mixed> */
    public array $options;

    /** @var array<int>|int|null */
    public $inputValue;

    /**
     * @param string                                     $name
     * @param bool                                       $multiple
     * @param bool                                       $required
     * @param string                                     $label
     * @param Collection<Collaborator>|Collaborator|null $value
     */
    public function __construct(public string $name, public bool $multiple = false, public bool $required = false, public string $label = '', public $value = null)
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render()
    {
        if ($this->multiple) {
            /** @var Collection<Collaborator>|null */
            $value = $this->value;
            // @phpstan-ignore-next-line
            $this->options = $value?->isNotEmpty() ? $value->sortBy('title')->reduce(function ($arr, $collaborator) {
                /** @var Collaborator $collaborator */
                if (empty($arr)) {
                    $arr = [];
                }
                $arr[$collaborator->id] = $collaborator->full_name;

                return $arr;
            }) : [];
            $this->inputValue = $value?->modelKeys() ?? [];
        } else {
            /** @var Collaborator|null */
            $value = $this->value;
            $this->options = $value ? [$value->id => $value->full_name] : [];
            $this->inputValue = $value?->id ?? null;
        }

        /** @var View */
        return view('components.collaborators.collaborator-selector');
    }
}
