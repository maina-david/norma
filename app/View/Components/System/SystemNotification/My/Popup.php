<?php

namespace App\View\Components\System\SystemNotification\My;

use App\Models\Auth\User;
use App\Models\System\SystemNotification;
use App\Services\Modules\CurrentModule;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class Popup extends Component
{
    /** @var SystemNotification|null */
    public ?SystemNotification $systemNotification;

    /** @var bool */
    public bool $shouldShow = false;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(protected CurrentModule $currentModule)
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render()
    {
        /** @var User $user */
        $user = Auth::user();

        $this->systemNotification = SystemNotification::active()
            ->notExpired()
            ->orderBy('id', 'desc')
            ->first();

        if ($this->systemNotification && !in_array($this->systemNotification->id, $user->settings['dismissed_notifications'] ?? [])) {
            if (is_null($this->systemNotification->modules)) {
                $this->shouldShow = true;
            } elseif (in_array($this->currentModule->get(), $this->systemNotification->modules)) {
                $this->shouldShow = true;
            }
        }

        /** @var View */
        return view('components.system.system-notification.my.popup');
    }
}
