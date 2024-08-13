<?php

namespace App\Observers\Customer;

use App\Actions\Customer\Libryo\HandleCreated;
use App\Events\Compilation\LibraryAttachedToLibryo;
use App\Mail\Notify\LibryoStreamDeactivated;
use App\Models\Compilation\Library;
use App\Models\Customer\Libryo;
use App\Models\Customer\Team;
use App\Services\Geo\Geohasher;
use App\Services\HTMLPurifierService;
use App\Stores\Customer\LibryoRequirementsCollectionStore;
use App\Stores\Customer\LibryoTeamStore;
use Illuminate\Support\Facades\Mail;

class LibryoObserver
{
    /**
     * @param Geohasher                         $geoHasher
     * @param HTMLPurifierService               $purifier
     * @param LibryoRequirementsCollectionStore $libryoRequirementsCollectionStore
     */
    public function __construct(
        protected Geohasher $geoHasher,
        protected HTMLPurifierService $purifier,
        protected LibryoRequirementsCollectionStore $libryoRequirementsCollectionStore
    ) {
    }

    /**
     * Listen to the Libryo created event.
     *
     * @param Libryo $libryo
     *
     * @return void
     */
    public function created(Libryo $libryo): void
    {
        $teams = Team::where('auto_add_to_place', true)->get(['id']);

        if ($teams->isNotEmpty()) {
            app(LibryoTeamStore::class)->attachTeams($libryo, $teams);
        }

        app(HandleCreated::class)->handle($libryo);
    }

    /**
     * Listen to the Libryo deleted event.
     *
     * @param Libryo $libryo
     *
     * @return void
     */
    public function deleted(Libryo $libryo): void
    {
        // TODO: add this back in
        // Comment::where('commentable_type', 'libryo')->where('commentable_id', $libryo->id)->delete();
    }

    /**
     * Listen to the Libryo saving event.
     *
     * @param Libryo $libryo
     *
     * @return void
     */
    public function saving(Libryo $libryo): void
    {
        if ($libryo->isDirty('geo_lat') || $libryo->isDirty('geo_lng')) {
            $libryo->geo_lat = $libryo->geo_lat ? trim($libryo->geo_lat) : $libryo->geo_lat;
            $libryo->geo_lng = $libryo->geo_lng ? trim($libryo->geo_lng) : $libryo->geo_lng;
            if ($libryo->geo_lat && $libryo->geo_lng) {
                $libryo->geohash = $this->geoHasher->encode($libryo->geo_lat . ', ' . $libryo->geo_lng);
            }
        }

        if ($libryo->isDirty('description')) {
            $libryo->description = $libryo->description
                ? $this->purifier->cleanSection($libryo->description)
                // @codeCoverageIgnoreStart
                : $libryo->description;
            // @codeCoverageIgnoreEnd
        }

        $q = $libryo->newQuery()
            ->when($libryo->id, function ($q) use ($libryo) {
                $q->where('id', '!=', $libryo->id);
            })
            ->where('organisation_id', $libryo->organisation_id)
            ->where('integration_id', $libryo->integration_id);

        // only validate when the integration ID has a value
        if ($libryo->integration_id && $libryo->isDirty('integration_id') && $q->exists()) {
            abort(409, __('exceptions.customer.duplicate_libryo'));
        }
    }

    /**
     * Listen to the Libryo saved event.
     *
     * @param Libryo $libryo
     *
     * @return void
     */
    public function saved(Libryo $libryo): void
    {
        if ($libryo->isDirty('location_id')) {
            $this->libryoRequirementsCollectionStore->syncCollectionsFromLocationId($libryo);
        }

        if ($libryo->isDirty('library_id') && $libryo->library_id) {
            $library = Library::find($libryo->library_id);

            if ($library) {
                LibraryAttachedToLibryo::dispatch($library, $libryo);
            }
        }

        // TODO: add this back in
        // if ($libryo->isDirty('library_id') && $libryo->library_id) {
        //    event(new EmbryoChangedForLibrary($libryo, $libryo->library));
        // }
    }

    /**
     * Listen to the Libryo updated event.
     *
     * @param Libryo $libryo
     *
     * @return void
     */
    public function updated(Libryo $libryo): void
    {
        if ($libryo->isDirty('location_id')) {
            $libryo->forgetCompilationCache();
        }

        $this->notifyChanges($libryo);

        // TODO: add this back in
        // if ($this->notRecompilationUpdate($libryo) && $libryo->canBeRecompiled()) {
        // $fromLocation = $libryo->getOriginal('location_id');
        // $toLocation = $libryo->location_id;

        // /** @var User $user */
        // $user = Auth::user();
        // event(new LocationChange($libryo, $user, $fromLocation, $toLocation));
        // }

        // TODO: add this back in
        // if ($libryo->isDirty('settings->modules->comply') && $libryo->assessEnabled()) {
        //     $this->responseRepo->createResponses($libryo);
        // }
    }

    // private function notRecompilationUpdate(Libryo $libryo)
    // {
    //     return $libryo->isDirty('location_id') && !$libryo->isDirty('needs_recompilation');
    // }

    /**
     * @param Libryo $libryo
     */
    public function notifyChanges(Libryo $libryo): void
    {
        if ($libryo->isDirty('deactivated') && $libryo->deactivated) {
            Mail::to(config('libryo.emails.libryo_deactivated'))->send(new LibryoStreamDeactivated($libryo));
        }
    }
}
