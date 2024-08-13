<?php

namespace App\View\Components\Notify\Notification;

use App\Enums\Notifications\NotificationType;
use App\Models\Auth\User;
use App\Models\Notify\Notification;
use App\Services\Notify\NotificationPresenter;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FeedItem extends Component
{
    /** @var NotificationType|null */
    public ?NotificationType $type;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(public User $user, public Notification $notification, protected NotificationPresenter $notificationPresenter)
    {
        $type = $notification->data['body']['type'] ?? null;
        /** @var NotificationType|null */
        $type = is_null($type) || $type === '' ? null : NotificationType::fromValue($type);
        $this->type = $type;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|Closure|string
     */
    public function render()
    {
        /** @var View */
        return view('components.notify.notification.feed-item');
    }

    public function showAvatar(Notification $notification): bool
    {
        return false;
    }

    public function getIcon(Notification $notification): string
    {
        return $this->notificationPresenter->getIcon($notification);
    }

    public function getLink(Notification $notification): string
    {
        return $this->notificationPresenter->getLink($notification);
    }

    public function getTitle(Notification $notification): string
    {
        return $this->notificationPresenter->getTitle($notification->presentViewData);
    }
}
