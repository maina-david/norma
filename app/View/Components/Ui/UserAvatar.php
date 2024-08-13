<?php

namespace App\View\Components\Ui;

use App\Models\Auth\User;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class UserAvatar extends Component
{
    /** @var User */
    public User $user;

    /** @var int */
    public int $dimensions;

    /**
     * Create a new component instance.
     *
     * @param User $user
     * @param int  $dimensions
     */
    public function __construct(User $user, int $dimensions = 8)
    {
        $this->user = $user;
        $this->dimensions = $dimensions;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|Closure|string
     */
    public function render()
    {
        return view('components.ui.user-avatar');
    }
}
