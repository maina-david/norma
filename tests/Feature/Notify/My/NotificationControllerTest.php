<?php

namespace Tests\Feature\Notify\My;

use App\Enums\Notifications\NotificationType;
use App\Models\Notify\Notification;
use App\Models\Tasks\Task;
use Tests\Feature\My\MyTestCase;

class NotificationControllerTest extends MyTestCase
{
    public function testIndex(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $routeName = 'my.notify.notifications.index';

        $task = Task::factory()->create();

        $notification = Notification::factory()->create([
            'notifiable_type' => 'Norma\Models\User',
            'notifiable_id' => $user->id,
            'data' => [
                'title' => ['translation_key' => 'notifications.notifications.task_overdue_incomplete_assignee_subject', 'variables' => []],
                'body' => ['task_id' => $task->id, 'type' => NotificationType::taskDue()->value],
            ],
        ]);

        $response = $this->get(route($routeName, ['readUnread' => 'all']))->assertSuccessful();
        $response->assertSee($task->title);

        $response = $this->get(route($routeName, ['readUnread' => 'unread']))->assertSuccessful();
        $response->assertSee($task->title);

        // and shouldn't show in unread feed anymore
        $response = $this->get(route($routeName, ['readUnread' => 'unread']))->assertSuccessful();
        $response->assertDontSee($task->title);
    }
}
