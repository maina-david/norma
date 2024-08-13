<?php

namespace App\View\Components\Ui;

use Closure;
use Illuminate\View\Component;

class Step extends Component
{
    /** @var string */
    public string $number;

    /** @var string */
    public string $name;

    /** @var string|null */
    public ?string $description;

    /** @var bool */
    public bool $active;

    /**
     * Create a new component instance.
     *
     * @param bool        $active
     * @param string      $number
     * @param string      $name
     * @param string|null $description
     */
    public function __construct(
        bool $active,
        string $number,
        string $name,
        ?string $description = null
    ) {
        $this->number = $number;
        $this->name = $name;
        $this->description = $description;
        $this->active = $active;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|Closure|string
     */
    public function render()
    {
        return view('components.ui.step');
    }
}
