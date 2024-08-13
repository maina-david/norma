<?php

namespace App\View\Components\Ui\Collaborate;

use App\Models\Auth\User;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class SubNav extends Component
{
    /**
     * @param array<int, mixed> $items
     */
    public function __construct(public array $items = [])
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render()
    {
        /** @var View */
        return view('components.ui.collaborate.sub-nav');
    }

    /**
     * @param array<string, mixed> $item
     *
     * @return bool
     */
    public function canSeeItem(array $item): bool
    {
        if (!isset($item['permissions'])) {
            return true;
        }
        /** @var User */
        $user = Auth::user();

        return $user->can($item['permissions']);
    }
}
