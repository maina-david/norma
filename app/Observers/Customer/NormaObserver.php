<?php

namespace App\Observers\Customer;

use App\Actions\Customer\Norma\HandleCreated;
use App\Events\Compilation\LibraryAttachedToNorma;
use App\Mail\Notify\NormaStreamDeactivated;
use App\Models\Compilation\Library;
use App\Models\Customer\Norma;
use App\Models\Customer\Team;
use App\Services\Geo\Geohasher;
use App\Services\HTMLPurifierService;
use App\Stores\Customer\NormaRequirementsCollectionStore;
use App\Stores\Customer\NormaTeamStore;
use Illuminate\Support\Facades\Mail;

class NormaObserver
{
    /**
     * @param Geohasher                         $geoHasher
     * @param HTMLPurifierService               $purifier
     * @param NormaRequirementsCollectionStore $normaRequirementsCollectionStore
     */
    public function __construct(
        protected Geohasher $geoHasher,
        protected HTMLPurifierService $purifier,
        protected NormaRequirementsCollectionStore $normaRequirementsCollectionStore
    ) {
    }

    /**
     * Listen to the Norma created event.
     *
     * @param Norma $norma
     *
     * @return void
     */
    public function created(Norma $norma): void
    {
        $teams = Team::where('auto_add_to_place', true)->get(['id']);

        if ($teams->isNotEmpty()) {
            app(NormaTeamStore::class)->attachTeams($norma, $teams);
        }

        app(HandleCreated::class)->handle($norma);
    }

    /**
     * Listen to the Norma deleted event.
     *
     * @param Norma $norma
     *
     * @return void
     */
    public function deleted(Norma $norma): void
    {
        // TODO: add this back in
        // Comment::where('commentable_type', 'norma')->where('commentable_id', $norma->id)->delete();
    }

    /**
     * Listen to the Norma saving event.
     *
     * @param Norma $norma
     *
     * @return void
     */
    public function saving(Norma $norma): void
    {
        if ($norma->isDirty('geo_lat') || $norma->isDirty('geo_lng')) {
            $norma->geo_lat = $norma->geo_lat ? trim($norma->geo_lat) : $norma->geo_lat;
            $norma->geo_lng = $norma->geo_lng ? trim($norma->geo_lng) : $norma->geo_lng;
            if ($norma->geo_lat && $norma->geo_lng) {
                $norma->geohash = $this->geoHasher->encode($norma->geo_lat . ', ' . $norma->geo_lng);
            }
        }

        if ($norma->isDirty('description')) {
            $norma->description = $norma->description
                ? $this->purifier->cleanSection($norma->description)
                // @codeCoverageIgnoreStart
                : $norma->description;
            // @codeCoverageIgnoreEnd
        }

        $q = $norma->newQuery()
            ->when($norma->id, function ($q) use ($norma) {
                $q->where('id', '!=', $norma->id);
            })
            ->where('organisation_id', $norma->organisation_id)
            ->where('integration_id', $norma->integration_id);

        // only validate when the integration ID has a value
        if ($norma->integration_id && $norma->isDirty('integration_id') && $q->exists()) {
            abort(409, __('exceptions.customer.duplicate_norma'));
        }
    }

    /**
     * Listen to the Norma saved event.
     *
     * @param Norma $norma
     *
     * @return void
     */
    public function saved(Norma $norma): void
    {
        if ($norma->isDirty('location_id')) {
            $this->normaRequirementsCollectionStore->syncCollectionsFromLocationId($norma);
        }

        if ($norma->isDirty('library_id') && $norma->library_id) {
            $library = Library::find($norma->library_id);

            if ($library) {
                LibraryAttachedToNorma::dispatch($library, $norma);
            }
        }

        // TODO: add this back in
        // if ($norma->isDirty('library_id') && $norma->library_id) {
        //    event(new EmbryoChangedForLibrary($norma, $norma->library));
        // }
    }

    /**
     * Listen to the Norma updated event.
     *
     * @param Norma $norma
     *
     * @return void
     */
    public function updated(Norma $norma): void
    {
        if ($norma->isDirty('location_id')) {
            $norma->forgetCompilationCache();
        }

        $this->notifyChanges($norma);

        // TODO: add this back in
        // if ($this->notRecompilationUpdate($norma) && $norma->canBeRecompiled()) {
        // $fromLocation = $norma->getOriginal('location_id');
        // $toLocation = $norma->location_id;

        // /** @var User $user */
        // $user = Auth::user();
        // event(new LocationChange($norma, $user, $fromLocation, $toLocation));
        // }

        // TODO: add this back in
        // if ($norma->isDirty('settings->modules->comply') && $norma->assessEnabled()) {
        //     $this->responseRepo->createResponses($norma);
        // }
    }

    // private function notRecompilationUpdate(Norma $norma)
    // {
    //     return $norma->isDirty('location_id') && !$norma->isDirty('needs_recompilation');
    // }

    /**
     * @param Norma $norma
     */
    public function notifyChanges(Norma $norma): void
    {
        if ($norma->isDirty('deactivated') && $norma->deactivated) {
            Mail::to(config('norma.emails.norma_deactivated'))->send(new NormaStreamDeactivated($norma));
        }
    }
}
