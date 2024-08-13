<?php

namespace App\Observers\Notify;

use App\Actions\Notify\LegalUpdate\HandleSaving;
use App\Actions\Notify\LegalUpdate\SyncMaintainedWorks;
use App\Jobs\Notify\GenerateLegalUpdateHighlights;
use App\Models\Notify\LegalUpdate;

class LegalUpdateObserver
{
    /**
     * Handle the LegalUpdate "saving" event.
     *
     * @param LegalUpdate $legalUpdate
     *
     * @return void
     */
    public function saving(LegalUpdate $legalUpdate)
    {
        app(HandleSaving::class)->handle($legalUpdate);
    }

    /**
     * Handle the LegalUpdate "created" event.
     *
     * @param \App\Models\Notify\LegalUpdate $legalUpdate
     *
     * @return void
     */
    public function created(LegalUpdate $legalUpdate)
    {
        if ($legalUpdate->created_from_doc_id) {
            (new SyncMaintainedWorks())->handle($legalUpdate->created_from_doc_id);
            dispatch(new GenerateLegalUpdateHighlights($legalUpdate));
        }
    }

    // /**
    //  * Handle the LegalUpdate "updated" event.
    //  *
    //  * @param  \App\Models\Notify\LegalUpdate  $legalUpdate
    //  * @return void
    //  */
    // public function updated(LegalUpdate $legalUpdate)
    // {
    //     //
    // }

    // /**
    //  * Handle the LegalUpdate "deleted" event.
    //  *
    //  * @param  \App\Models\Notify\LegalUpdate  $legalUpdate
    //  * @return void
    //  */
    // public function deleted(LegalUpdate $legalUpdate)
    // {
    //     //
    // }

    // /**
    //  * Handle the LegalUpdate "restored" event.
    //  *
    //  * @param  \App\Models\Notify\LegalUpdate  $legalUpdate
    //  * @return void
    //  */
    // public function restored(LegalUpdate $legalUpdate)
    // {
    //     //
    // }

    // /**
    //  * Handle the LegalUpdate "force deleted" event.
    //  *
    //  * @param  \App\Models\Notify\LegalUpdate  $legalUpdate
    //  * @return void
    //  */
    // public function forceDeleted(LegalUpdate $legalUpdate)
    // {
    //     //
    // }
}
