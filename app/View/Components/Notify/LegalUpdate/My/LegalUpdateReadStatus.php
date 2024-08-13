<?php

namespace App\View\Components\Notify\LegalUpdate\My;

use App\Models\Auth\User;
use App\Models\Notify\LegalUpdate;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class LegalUpdateReadStatus extends Component
{
    /** @var string */
    public string $color = 'negative';

    /**
     * Can't typehint the models, as the container inject them otherwise,
     * and the is_null always returns true.
     *
     * @param LegalUpdate|null $update
     * @param User|null        $user
     */
    public function __construct(public $update = null, public $user = null)
    {
        if (!is_null($update)) {
            $this->setColorByUpdate();
        } else {
            $this->setColorByUser();
        }
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render()
    {
        /** @var View */
        return view('components.notify.legal-update.my.legal-update-read-status');
    }

    public function setColorByUpdate(): void
    {
        if (is_null($this->update)) {
            // @codeCoverageIgnoreStart
            // for the tailwind JIT compiler to detect - so not used in here
            $tailwindColors = ['bg-libryo-gray-400', 'bg-positive', 'bg-warning', 'bg-negative'];
            $this->color = 'libryo-gray-400';

            return;
            // @codeCoverageIgnoreEnd
        }
        if ($this->update->user_read_status === true && $this->update->user_understood_status === true) {
            $this->color = 'positive';

            return;
        }
        if ($this->update->user_read_status === true && $this->update->user_understood_status === false) {
            $this->color = 'warning';

            return;
        }
        if ($this->update->user_read_status === false && $this->update->user_understood_status === false) {
            $this->color = 'negative';

            return;
        }
        // if set to null, there is no user relation
        // @codeCoverageIgnoreStart
        $this->color = 'libryo-gray-400';

        // @codeCoverageIgnoreEnd
    }

    public function setColorByUser(): void
    {
        if (is_null($this->user?->pivot)) {
            // @codeCoverageIgnoreStart
            $this->color = 'libryo-gray-400';

            return;
            // @codeCoverageIgnoreEnd
        }
        if ($this->user->pivot->read_status === true && $this->user->pivot->understood_status === true) {
            $this->color = 'positive';

            return;
        }
        if ($this->user->pivot->read_status === true && $this->user->pivot->understood_status === false) {
            $this->color = 'warning';

            return;
        }
        if ($this->user->pivot->read_status === false && $this->user->pivot->understood_status === false) {
            $this->color = 'negative';

            return;
        }
        // if set to null, there is no user relation
        // @codeCoverageIgnoreStart
        $this->color = 'libryo-gray-400';
    }
    // @codeCoverageIgnoreEnd
}
