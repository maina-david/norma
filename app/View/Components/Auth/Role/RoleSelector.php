<?php

namespace App\View\Components\Auth\Role;

use App\Models\Auth\Role;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class RoleSelector extends Component
{
    /** @var array<int, string> */
    public array $roles;

    /**
     * Create a new component instance.
     *
     * @param string                        $name
     * @param string                        $label
     * @param array<array-key,int>|int|null $value
     */
    public function __construct(public string $name = 'role_id', public string $label = '', public array|int|null $value = null)
    {
        $this->roles = ['' => '-'] + Role::collaborate()->orderBy('title')->pluck('title', 'id')->toArray();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render()
    {
        /** @var View */
        return view('components.auth.role.role-selector');
    }
}
