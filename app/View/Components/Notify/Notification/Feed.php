<?php

namespace App\View\Components\Notify\Notification;

use App\Models\Auth\User;
use App\Services\Notify\NotificationPresenter;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Feed extends Component
{
    /** @var LengthAwarePaginator */
    public LengthAwarePaginator $notifications;

    /**
     * @param User                  $user
     * @param bool                  $unread
     * @param NotificationPresenter $notificationPresenter
     */
    public function __construct(public User $user, public bool $unread, protected NotificationPresenter $notificationPresenter)
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|Closure|string
     */
    public function render()
    {
        $this->notifications = $this->user->notifications()
            ->when($this->unread, fn ($q) => $q->unread())
            ->paginate($this->unread ? 5 : 15);

        $this->notificationPresenter->gatherViewData($this->notifications);

        if ($this->unread) {
            foreach ($this->notifications as $notification) {
                $notification->markAsRead();
            }
        }

        /** @var View */
        return view('components.notify.notification.feed');
    }
}
