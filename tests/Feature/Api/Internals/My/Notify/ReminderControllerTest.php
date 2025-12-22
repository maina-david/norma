<?php

namespace Tests\Feature\Api\Internals\My\Notify;

use App\Models\Notify\Reminder;
use App\Models\Tasks\Task;
use Tests\Feature\My\MyTestCase;

class ReminderControllerTest extends MyTestCase
{
    public function testListing(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $task = Task::factory()->create();
        $reminder = Reminder::factory()->create([
            'author_id' => $user->id,
            'remindable_type' => 'task',
            'remindable_id' => $task->id,
        ]);

        $this->getJson(route('api.my.reminders.related.index', ['relation' => 'task', 'id' => $task->id]))
            ->assertSuccessful()
            ->assertJson([
                'data' => [
                    ['id' => $reminder->id],
                ],
            ]);
    }

    public function testStoreAndDestroy(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $user->update(['timezone' => 'UTC']);
        $task = Task::factory()->create();

        $payload = [
            'remind_on_date' => '2024-01-01',
            'remind_on_time' => '00:00',
            'remind_whom' => null,
            'notification_config' => [1, 2],
        ];

        $this->assertDatabaseMissing(Reminder::class, [
            'remindable_id' => $task->id,
            'remind_on' => '2024-01-01 00:00:00',
        ]);

        $this->postJson(route('api.my.reminders.related.store', ['relation' => 'task', 'id' => $task->id]), $payload)
            ->assertSuccessful()
            ->assertJson([
                'data' => [
                    'remind_on' => '2024-01-01T00:00:00.000000Z',
                ],
            ]);

        $this->assertDatabaseHas(Reminder::class, [
            'remindable_id' => $task->id,
            'remind_on' => '2024-01-01 00:00:00',
            'deleted_at' => null,
        ]);

        $reminder = Reminder::where([
            'remindable_id' => $task->id,
            'remind_on' => '2024-01-01 00:00:00',
        ])->first();

        $this->deleteJson(route('api.my.reminders.destroy', ['reminder' => $reminder->id]))
            ->assertSuccessful()
            ->assertJson([]);

        $this->assertDatabaseMissing(Reminder::class, [
            'remindable_id' => $task->id,
            'remind_on' => '2024-01-01 00:00:00',
            'deleted_at' => null,
        ]);
    }
}
