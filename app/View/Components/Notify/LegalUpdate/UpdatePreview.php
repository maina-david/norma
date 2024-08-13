<?php

namespace App\View\Components\Notify\LegalUpdate;

use App\Models\Notify\LegalUpdate;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class UpdatePreview extends Component
{
    public bool $docPreview = false;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(public LegalUpdate $legalUpdate)
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render()
    {
        if (!is_null($this->legalUpdate->createdFromDoc) && $this->legalUpdate->createdFromDoc->tocItems()->count() > 0) {
            $this->docPreview = true;
        }

        /** @var View */
        return view('components.notify.legal-update.update-preview');
    }
}
