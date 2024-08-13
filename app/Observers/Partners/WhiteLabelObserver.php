<?php

namespace App\Observers\Partners;

use App\Models\Partners\WhiteLabel;

class WhiteLabelObserver
{
    public function saving(WhiteLabel $label): void
    {
        $label->urls = $label->urls ?? [];
        $label->theme = $label->theme ?? [];
    }

    /**
     * Listen to the saved event.
     *
     * @param WhiteLabel $label
     *
     * @return void
     */
    public function saved(WhiteLabel $label): void
    {
        WhiteLabel::cache();
    }

    /**
     * Listen to the saved event.
     *
     * @todo remove code coverage ignore once covered
     *
     * @codeCoverageIgnore
     *
     * @param WhiteLabel $label
     *
     * @return void
     */
    public function deleted(WhiteLabel $label): void
    {
        WhiteLabel::cache();
    }

    /**
     * Listen to the saved event.
     *
     * @todo remove code coverage ignore once covered
     *
     * @codeCoverageIgnore
     *
     * @param WhiteLabel $label
     *
     * @return void
     */
    public function restored(WhiteLabel $label): void
    {
        WhiteLabel::cache();
    }
}
