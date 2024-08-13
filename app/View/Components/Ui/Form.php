<?php

namespace App\View\Components\Ui;

use Closure;
use Illuminate\View\Component;

class Form extends Component
{
    /** @var string|null */
    public ?string $method;

    /** @var string|null */
    public ?string $action;

    /**
     * Create a new component instance.
     *
     * @param string|null $method
     * @param string|null $action
     */
    public function __construct(?string $method = null, ?string $action = null)
    {
        $this->method = !is_null($method) ? strtolower($method) : null;
        $this->action = $action;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|Closure|string
     */
    public function render()
    {
        return view('components.ui.form');
    }
}
