<?php

namespace Tests\Feature\Console\Notify;

use App\Enums\Notify\LegalUpdatePublishedStatus;
use App\Events\Notify\LibraryAttachedToLegalUpdate;
use App\Events\Notify\NormaAttachedToLegalUpdate;
use App\Jobs\Notify\NotifyLibraries;
use App\Models\Auth\User;
use App\Models\Compilation\Library;
use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use App\Models\Customer\Norma;
use App\Models\Notify\LegalUpdate;
use App\Models\Notify\LegalUpdateNotified;
use App\Models\Notify\Pivots\LegalUpdateLibrary;
use App\Models\Notify\Pivots\LegalUpdateNorma;
use App\Models\Notify\Pivots\LegalUpdateUser;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use stdClass;
use Tests\TestCase;

class NotifyLibrariesTest extends TestCase
{
    /**
     * Get the items required for the test.
     *
     * @return stdClass
     */
    protected function getRequiredEntities(): stdClass
    {
        $work = Work::factory()->create();
        $reference = Reference::factory()->create(['work_id' => $work->id]);
        $user = User::factory()->create();
        $library = Library::factory()->create();
        $norma = Norma::factory()->create(['library_id' => $library->id]);

        $norma->users()->attach($user->id);
        $norma->references()->attach($reference->id);
        $library->references()->attach($reference->id);

        $update = LegalUpdate::factory()->create([
            'release_at' => now()->subHour(),
            'work_id' => $work->id,
            'notify_reference_id' => $reference->id,
            'status' => LegalUpdatePublishedStatus::PUBLISHED->value,
        ]);

        return (object) compact('work', 'library', 'norma', 'update', 'user');
    }

    /**
     * @return void
     */
    public function testItDispatchesTheJob(): void
    {
        Bus::fake([NotifyLibraries::class]);

        Bus::assertNothingDispatched();

        $this->artisan('norma:notify-libraries')->assertSuccessful();

        Bus::assertDispatched(NotifyLibraries::class);
    }

    /**
     * @return void
     */
    public function testItProcessesReadyUpdates(): void
    {
        $entities = $this->getRequiredEntities();

        $this->assertCount(1, LegalUpdate::unnotifiedReadyForRelease()->get());

        $this->artisan('norma:notify-libraries')->assertSuccessful();

        $this->assertCount(0, LegalUpdate::unnotifiedReadyForRelease()->get());

        $this->assertDatabaseHas((new LegalUpdateNotified())->getTable(), [
            'register_notification_id' => $entities->update->id,
        ]);
    }

    /**
     * @return void
     */
    public function testItNotifiesLibraries(): void
    {
        $entities = $this->getRequiredEntities();

        $this->assertDatabaseMissing((new LegalUpdateLibrary())->getTable(), [
            'register_notification_id' => $entities->update->id,
            'library_id' => $entities->library->id,
        ]);

        Event::fake([
            LibraryAttachedToLegalUpdate::class,
        ]);

        Event::assertNotDispatched(LibraryAttachedToLegalUpdate::class);

        $this->artisan('norma:notify-libraries')->assertSuccessful();

        Event::assertDispatched(function (LibraryAttachedToLegalUpdate $event) use ($entities) {
            return $event->update->id === $entities->update->id && $event->library->id === $entities->library->id;
        });

        $this->assertDatabaseHas((new LegalUpdateLibrary())->getTable(), [
            'register_notification_id' => $entities->update->id,
            'library_id' => $entities->library->id,
        ]);
    }

    /**
     * @return void
     */
    public function testItNotifiesNormas(): void
    {
        $entities = $this->getRequiredEntities();

        Event::fake([
            NormaAttachedToLegalUpdate::class,
        ]);

        Event::assertNotDispatched(NormaAttachedToLegalUpdate::class);

        $this->assertDatabaseMissing((new LegalUpdateNorma())->getTable(), [
            'register_notification_id' => $entities->update->id,
            'place_id' => $entities->norma->id,
        ]);

        $this->artisan('norma:notify-libraries')->assertSuccessful();

        Event::assertDispatched(function (NormaAttachedToLegalUpdate $event) use ($entities) {
            return $event->update->id === $entities->update->id && $event->norma->id === $entities->norma->id;
        });

        $this->assertDatabaseHas((new LegalUpdateNorma())->getTable(), [
            'register_notification_id' => $entities->update->id,
            'place_id' => $entities->norma->id,
        ]);
    }

    /**
     * @return void
     */
    public function testItNotifiesUsers(): void
    {
        $entities = $this->getRequiredEntities();

        $this->assertDatabaseMissing((new LegalUpdateUser())->getTable(), [
            'register_notification_id' => $entities->update->id,
            'user_id' => $entities->user->id,
        ]);

        $this->artisan('norma:notify-libraries')->assertSuccessful();

        $this->assertDatabaseHas((new LegalUpdateUser())->getTable(), [
            'register_notification_id' => $entities->update->id,
            'user_id' => $entities->user->id,
        ]);
    }
}
