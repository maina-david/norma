<?php

namespace Tests\Unit\Events\Auth\UserActivity;

use App\Enums\Auth\UserActivityType;
use App\Events\Auth\UserActivity\CreatedReminder;
use App\Models\Auth\User;
use App\Models\Corpus\Reference;
use App\Models\Notify\Reminder;
use App\Models\Storage\My\File;
use App\Models\Tasks\Task;
use Tests\Feature\My\MyTestCase;

class CreatedReminderTest extends MyTestCase
{
    public function testCreatedReminder(): void
    {
        $user = User::factory()->create();
        $reference = Reference::factory()->create();
        $file = File::factory()->create();
        $task = Task::factory()->create();

        $reminder = Reminder::factory()->create(['remindable_type' => 'task', 'remindable_id' => $task->id]);
        $event = new CreatedReminder($user, $reminder->id);
        $this->assertTrue($event->getActivityType()->is(UserActivityType::taskReminder()));

        $reminder = Reminder::factory()->create(['remindable_type' => 'file', 'remindable_id' => $file->id]);
        $event = new CreatedReminder($user, $reminder->id);
        $this->assertTrue($event->getActivityType()->is(UserActivityType::documentReminder()));

        $reminder = Reminder::factory()->create(['remindable_type' => 'register_item', 'remindable_id' => $reference->id]);
        $event = new CreatedReminder($user, $reminder->id);
        $this->assertTrue($event->getActivityType()->is(UserActivityType::referenceReminder()));
    }
}
