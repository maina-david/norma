<?php

namespace App\Traits\Livewire;

use Illuminate\View\View;

trait UsesPlaceholder
{
    /**
     * The number of rows to display in the placeholder.
     *
     * @codeCoverageIgnore
     *
     * @return int
     */
    protected function placeholderRows(): int
    {
        return 3;
    }

    /**
     * The loading placeholder.
     *
     * @return \Illuminate\View\View
     */
    public function placeholder(): View
    {
        /** @var View */
        return view('partials.ui.render-skeleton', [
            'rows' => $this->placeholderRows(),
            'noCircle' => true,
            'flat' => true,
            'classes' => 'px-4 h-full',
        ]);
    }
}
