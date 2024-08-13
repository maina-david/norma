<?php

namespace App\Listeners\Notify;

use App\Events\Compilation\LibraryAttachedToLibryo;
use App\Events\Notify\LibraryAttachedToLegalUpdate;
use App\Events\Notify\LibryoAttachedToLegalUpdate;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;
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
            LibryoAttachedToLegalUpdate::class => 'onLibryoAttached',
            LibraryAttachedToLibryo::class => 'onLibraryAttachedToLibryo',
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
        $libryos = $event->library->applicableLibryos();
        $libryos = $libryos->filter(fn ($l) => $l->isActive());
        $this->store->attachLibryos($event->update, $libryos);
    }

    /**
     * Handle the event of user attachment.
     *
     * @param LibryoAttachedToLegalUpdate $event
     *
     * @return void
     */
    public function onLibryoAttached(LibryoAttachedToLegalUpdate $event): void
    {
        $event->update->load(['legalDomains']);

        $users = User::libryoAccess($event->libryo)
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
     * @param LibraryAttachedToLibryo $event
     *
     * @return void
     */
    public function onLibraryAttachedToLibryo(LibraryAttachedToLibryo $event): void
    {
        $event->libryo->legalUpdates()->detach();

        $libryos = $event->libryo->newCollection();
        $libryos->add($event->libryo);

        /** @var Collection<Libryo> $currentLibryo */
        $currentLibryo = $event->libryo->newCollection()->toBase();
        $currentLibryo->push($event->libryo);

        foreach ($event->libryo->getLibraryAncestors() as $library) {
            $library->legalUpdates()->chunk(200, function ($updates) use ($currentLibryo) {
                $updates->each(function ($update) use ($currentLibryo) {
                    $this->store->attachLibryos($update, $currentLibryo);
                });
            });
        }
    }
}
