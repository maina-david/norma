<?php

namespace Tests\Feature\Console\Notify;

use App\Enums\Tasks\TaskUser;
use App\Jobs\Notify\SendReminderNotifications;
use App\Mail\Notify\ReminderEmail;
use App\Models\Auth\User;
use App\Models\Compilation\Library;
use App\Models\Corpus\Reference;
use App\Models\Customer\Libryo;
use App\Models\Notify\Reminder;
use App\Models\Tasks\Task;
use App\Notifications\Notify\ReminderNotification;
use Exception;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SendReminderNotificationsTest extends TestCase
{
    /**
     * @return void
     */
    public function testItDispatchesTheJob(): void
    {
        $task = Task::factory()->create();

        $task->reminders()->create(array_merge(Reminder::factory()->raw(), [
            'remind_on' => now()->subDay(),
        ]));

        Bus::fake([SendReminderNotifications::class]);

        Bus::assertNothingDispatched();

        $this->artisan('libryo:send-reminders')->assertSuccessful();

        Bus::assertDispatched(SendReminderNotifications::class);
    }

    /**
     * @throws Exception
     *
     * @return void
     */
    public function testItRemindsTaskUsers(): void
    {
        $author = User::factory()->create();
        $watcher = User::factory()->create();
        $assignee = User::factory()->create();
        $task = Task::factory()->create(['assigned_to_id' => $assignee->id, 'author_id' => $author->id]);
        $task->watchers()->attach($watcher->id);

        $reminder = $task->reminders()->create(array_merge(Reminder::factory()->raw(), [
            'remind_on' => now()->subDay(),
            'notification_config' => TaskUser::options(),
        ]));

        Notification::fake();
        Notification::assertNothingSent();

        $reminder->remindable_id = 1234;
        $reminder->save();

        $this->artisan('libryo:send-reminders')->assertSuccessful();

        Notification::assertNotSentTo([$author, $watcher, $assignee], ReminderNotification::class);

        $reminder->remindable_id = $task->id;
        $reminder->save();

        $this->artisan('libryo:send-reminders')->assertSuccessful();

        Notification::assertSentTo([
            $author, $watcher, $assignee,
        ], fn (ReminderNotification $notification) => $notification->reminder->id === $reminder->id);
    }

    /**
     * @throws Exception
     *
     * @return void
     */
    public function testItRemindsReferenceUsers(): void
    {
        $author = User::factory()->create();
        $reference = Reference::factory()->create();

        $reminder = $reference->reminders()->create(array_merge(Reminder::factory()->raw(), [
            'remind_on' => now()->subDay(),
            'author_id' => $author->id,
        ]));

        Notification::fake();

        Notification::assertNothingSent();

        $this->artisan('libryo:send-reminders')->assertSuccessful();

        Notification::assertSentTo(
            [$author],
            fn (ReminderNotification $notification) => $notification->reminder->id === $reminder->id
        );
    }

    /**
     * @throws Exception
     *
     * @return void
     */
    public function testItRemindsUsersInLibryo(): void
    {
        $author = User::factory()->create();
        $reference = Reference::factory()->create();
        $user = User::factory()->create();
        $library = Library::factory()->create();
        $libryo = Libryo::factory()->create(['library_id' => $library->id]);

        $libryo->users()->attach([$author->id, $user->id]);
        $libryo->organisation->users()->attach([$author->id, $user->id]);

        $reminder = $reference->reminders()->create(array_merge(Reminder::factory()->raw(), [
            'remind_on' => now()->subDay(),
            'author_id' => $author->id,
            'place_id' => $libryo->id,
        ]));

        Notification::fake();

        Notification::assertNothingSent();

        $this->artisan('libryo:send-reminders')->assertSuccessful();

        Notification::assertSentTo(
            [$author, $user],
            fn (ReminderNotification $notification) => $notification->reminder->id === $reminder->id
        );
    }

    /**
     * @throws Exception
     *
     * @return void
     */
    public function testItRendersMails(): void
    {
        $author = User::factory()->create();
        $reference = Reference::factory()->create();
        $user = User::factory()->create();
        $library = Library::factory()->create();
        $libryo = Libryo::factory()->create(['library_id' => $library->id]);

        $libryo->users()->attach([$author->id, $user->id]);
        $libryo->organisation->users()->attach([$author->id, $user->id]);

        $reminder = $reference->reminders()->create(array_merge(Reminder::factory()->raw(), [
            'remind_on' => now()->subDay(),
            'author_id' => $author->id,
            'place_id' => $libryo->id,
        ]));

        $mail = new ReminderEmail($author, $reminder);

        $mail->assertSeeInHtml('You set a reminder to go off today.');

        $mail = new ReminderEmail($user, $reminder);
        $mail->assertSeeInHtml('set a reminder to go off today and thought you needed to know about it.');
    }
}
