<?php

namespace App\Listeners\Notify;

use App\Events\Compilation\LibraryAttachedToNorma;
use App\Events\Notify\LibraryAttachedToLegalUpdate;
use App\Events\Notify\NormaAttachedToLegalUpdate;
use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Stores\Notify\LegalUpdateStore;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Events\Dispatcher;

class LegalUpdateAttachmentsSubscriber implements ShouldQueue
{
    /**
     * The name of the queue the job should be sent to.
     *
     * @var string
     */
    public string $queue = 'notifications';

    /**
     * Create the event listener.
     *
     * @param LegalUpdateStore $store
     */
    public function __construct(protected LegalUpdateStore $store)
    {
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param Dispatcher $events
     *
     * @return array<class-string, string>
     */
    public function subscribe($events): array
    {
        return [
            LibraryAttachedToLegalUpdate::class => 'onLibraryAttached',
            NormaAttachedToLegalUpdate::class => 'onNormaAttached',
            LibraryAttachedToNorma::class => 'onLibraryAttachedToNorma',
        ];
    }

    /**
     * Handle the event of library attachment.
     *
     * @param LibraryAttachedToLegalUpdate $event
     *
     * @return void
     */
    public function onLibraryAttached(LibraryAttachedToLegalUpdate $event): void
    {
        $normas = $event->library->applicableNormas();
        $normas = $normas->filter(fn ($l) => $l->isActive());
        $this->store->attachNormas($event->update, $normas);
    }

    /**
     * Handle the event of user attachment.
     *
     * @param NormaAttachedToLegalUpdate $event
     *
     * @return void
     */
    public function onNormaAttached(NormaAttachedToLegalUpdate $event): void
    {
        $event->update->load(['legalDomains']);

        $users = User::normaAccess($event->norma)
            ->whereNotNull('email')
            ->where('active', true)
            ->get()
            ->filter(function ($user) use ($event) {
                return $user->shouldReceiveLegalUpdate($event->update->legalDomains);
            });

        $this->store->attachUsers($event->update, $users);
    }

    /**
     * Listen to the attachment event.
     *
     * @param LibraryAttachedToNorma $event
     *
     * @return void
     */
    public function onLibraryAttachedToNorma(LibraryAttachedToNorma $event): void
    {
        $event->norma->legalUpdates()->detach();

        $normas = $event->norma->newCollection();
        $normas->add($event->norma);

        /** @var Collection<Norma> $currentNorma */
        $currentNorma = $event->norma->newCollection()->toBase();
        $currentNorma->push($event->norma);

        foreach ($event->norma->getLibraryAncestors() as $library) {
            $library->legalUpdates()->chunk(200, function ($updates) use ($currentNorma) {
                $updates->each(function ($update) use ($currentNorma) {
                    $this->store->attachNormas($update, $currentNorma);
                });
            });
        }
    }
}
