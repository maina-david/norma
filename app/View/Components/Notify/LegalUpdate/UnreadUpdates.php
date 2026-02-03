<?php

namespace App\View\Components\Notify\LegalUpdate;

use App\Models\Auth\User;
use App\Models\Notify\LegalUpdate;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\Component;

class UnreadUpdates extends Component
{
    /** @var Builder */
    public Builder $query;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(public User $user, public int $perPage = 10)
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render()
    {
        $this->query = $this->user->unreadLegalUpdates()
//            ->whereHas('normas', fn ($query) => $query->active()->userHasAccess($this->user))
            ->getQuery()
            ->orderByRaw('COALESCE(release_at,' . (new LegalUpdate())->getTable() . '.created_at) DESC')
            ->with([
                //                'work',
                'primaryLocation:id,flag',
                'legalDomains:id,title',
            ]);

        /** @var View */
        return view('components.notify.legal-update.unread-updates');
    }
}
