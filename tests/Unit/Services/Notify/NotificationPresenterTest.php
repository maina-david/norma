<?php

namespace Tests\Unit\Services\Notify;

use App\Enums\Notifications\NotificationType;
use App\Enums\Tasks\TaskPriority;
use App\Enums\Tasks\TaskStatus;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Auth\User;
use App\Models\Comments\Comment;
use App\Models\Corpus\Reference;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Notify\LegalUpdate;
use App\Models\Notify\Notification;
use App\Models\Storage\My\File;
use App\Models\Tasks\Task;
use App\Services\Notify\NotificationPresenter;
use Tests\TestCase;

class NotificationPresenterTest extends TestCase
{
    public function testGatherViewData(): void
    {
        $service = app(NotificationPresenter::class);
        $update = LegalUpdate::factory()->create();
        $user = User::factory()->create();
        $task = Task::factory()->create();
        $commentUpdate = Comment::factory()->create(['commentable_type' => 'register_notification', 'commentable_id' => $update->id]);

        $body = [
            'type' => NotificationType::mention()->value,
            'comment_id' => $commentUpdate->id,
            'task_id' => $task->id,
            'changed_by_id' => $user->id,
            'from_user_id' => $user->id,
            'to_user_id' => $user->id,
            'assigned_to' => $user->id,
            'assigned_by' => $user->id,
            'completed_by_id' => $user->id,
            'task_status_from' => TaskStatus::notStarted()->value,
            'task_status_to' => TaskStatus::inProgress()->value,
            'task_priority_from' => TaskPriority::low()->value,
            'task_priority_to' => TaskPriority::high()->value,
        ];

        $notifications = Notification::factory(2)->create([
            'data' => [
                'title' => ['translation_key' => '', 'variables' => ['user' => $user->id]],
                'body' => $body,
            ],
        ]);

        $notifications = $service->gatherViewData($notifications);
        $this->assertTrue($commentUpdate->is($notifications[0]->presentViewData['body']['comment']));
        $this->assertTrue($notifications[0]->presentViewData['title']['variables']['user'] === $user->fullName);
        $this->assertTrue($task->is($notifications[0]->presentViewData['body']['task']));
        $this->assertTrue($user->fullName === $notifications[0]->presentViewData['body']['changed_by']);
        $this->assertTrue($user->fullName === $notifications[0]->presentViewData['body']['from_user']);
        $this->assertTrue($user->fullName === $notifications[0]->presentViewData['body']['to_user']);
        $this->assertTrue($user->fullName === $notifications[0]->presentViewData['body']['assigned_to']);
        $this->assertTrue($user->fullName === $notifications[0]->presentViewData['body']['assigned_by']);
        $this->assertTrue($user->fullName === $notifications[0]->presentViewData['body']['completed_by']);
        $this->assertTrue(TaskStatus::lang()[TaskStatus::notStarted()->value] === $notifications[0]->presentViewData['body']['task_status_from']);
        $this->assertTrue(TaskStatus::lang()[TaskStatus::inProgress()->value] === $notifications[0]->presentViewData['body']['task_status_to']);
        $this->assertTrue(TaskPriority::lang()[TaskPriority::low()->value] === $notifications[0]->presentViewData['body']['task_priority_from']);
        $this->assertTrue(TaskPriority::lang()[TaskPriority::high()->value] === $notifications[0]->presentViewData['body']['task_priority_to']);

        // test deleted users - 0 will result in user not found
        // test empty statuses and priorities
        $notifications = $service->gatherViewData($notifications);
        $notifications = Notification::factory(2)->create([
            'data' => [
                'title' => ['translation_key' => '', 'variables' => ['user' => 0]],
                'body' => [
                    'changed_by_id' => 0,
                    'from_user_id' => 0,
                    'to_user_id' => 0,
                    'assigned_to' => 0,
                    'assigned_by' => 0,
                    'completed_by_id' => 0,
                    'task_status_from' => 10000, // doesn't exist
                    'task_status_to' => 10000, // doesn't exist
                    'task_priority_from' => 10000, // doesn't exist
                    'task_priority_to' => 10000, // doesn't exist
                ],
            ],
        ]);
        $notifications = $service->gatherViewData($notifications);
        $this->assertTrue($notifications[0]->presentViewData['title']['variables']['user'] === 'Unknown');
        $this->assertTrue('Unknown' === $notifications[0]->presentViewData['body']['changed_by']);
        $this->assertTrue('Unknown' === $notifications[0]->presentViewData['body']['from_user']);
        $this->assertTrue('Unknown' === $notifications[0]->presentViewData['body']['to_user']);
        $this->assertTrue('Unknown' === $notifications[0]->presentViewData['body']['assigned_to']);
        $this->assertTrue('Unknown' === $notifications[0]->presentViewData['body']['assigned_by']);
        $this->assertTrue('Unknown' === $notifications[0]->presentViewData['body']['completed_by']);

        $this->assertTrue('Unknown' === $notifications[0]->presentViewData['body']['task_status_from']);
        $this->assertTrue('Unknown' === $notifications[0]->presentViewData['body']['task_status_to']);
        $this->assertTrue('Unknown' === $notifications[0]->presentViewData['body']['task_priority_from']);
        $this->assertTrue('Unknown' === $notifications[0]->presentViewData['body']['task_priority_to']);
    }

    public function testGetIcon(): void
    {
        $service = app(NotificationPresenter::class);
        $notification = Notification::factory()->create(['data' => ['body' => ['type' => NotificationType::mention()->value]]]);
        $icon = $service->getIcon($notification);
        $this->assertTrue($icon === 'at');

        foreach ([
            NotificationType::taskAssigned(),
            NotificationType::taskAssigneeChanged(),
            NotificationType::taskCompleted(),
            NotificationType::taskDueDateChanged(),
            NotificationType::taskPriorityChanged(),
            NotificationType::taskStatusChanged(),
            NotificationType::taskTitleChanged(),
        ] as $type) {
            $notification = Notification::factory()->create(['data' => ['body' => ['type' => $type->value]]]);
            $icon = $service->getIcon($notification);
            $this->assertTrue($icon === 'tasks', 'Failed assertion for: ' . $type->value);
        }

        $notification = Notification::factory()->create(['data' => ['body' => ['type' => NotificationType::taskDue()->value]]]);
        $icon = $service->getIcon($notification);
        $this->assertTrue($icon === 'alarm-exclamation');

        $notification = Notification::factory()->create(['data' => ['body' => ['type' => NotificationType::reminder()->value]]]);
        $icon = $service->getIcon($notification);
        $this->assertTrue($icon === 'alarm-exclamation');

        $notification = Notification::factory()->create(['data' => ['body' => ['type' => NotificationType::reply()->value]]]);
        $icon = $service->getIcon($notification);
        $this->assertTrue($icon === 'comment');

        $notification = Notification::factory()->create(['data' => ['body' => ['type' => 0]]]);
        $icon = $service->getIcon($notification);
        $this->assertTrue($icon === 'bells');
    }

    public function testGetLink(): void
    {
        $service = app(NotificationPresenter::class);
        $task = Task::factory()->create();
        $notification = Notification::factory()->create(['data' => ['body' => ['type' => NotificationType::taskDue()->value]]]);
        $notification->presentViewData = $notification->data;
        $notification->presentViewData['body']['task'] = $task;
        $this->assertStringContainsString('/tasks/' . $task->hash_id, $service->getLink($notification));
        $notification->presentViewData['body']['task'] = null;
        $this->assertTrue($service->getLink($notification) === '');

        $update = LegalUpdate::factory()->create();
        $user = User::factory()->create();
        $norma = Norma::factory()->create();
        $commentUpdate = Comment::factory()->for($norma)->create(['commentable_type' => 'register_notification', 'commentable_id' => $update->id]);
        $notification = Notification::factory()->create(['data' => ['body' => ['type' => NotificationType::mention()->value]]]);
        $notification->presentViewData = $notification->data;
        $notification->presentViewData['body']['comment'] = $commentUpdate;
        $link = $service->getLink($notification);
        $this->assertStringContainsString('/normas/activate/' . $norma->id, $link);
        $this->assertStringContainsString('legal-updates' . urlencode('/') . $update->id, $link);

        $notification->presentViewData['body']['comment'] = Comment::factory()->for($norma)->create(['commentable_type' => 'task', 'commentable_id' => $task->id]);
        $link = $service->getLink($notification);
        $this->assertStringContainsString(urlencode('/') . 'tasks' . urlencode('/') . $task->hash_id, $link);

        $reference = Reference::factory()->create();
        $commentReference = Comment::factory()->for($norma)->create(['commentable_type' => 'register_item', 'commentable_id' => $reference->id]);
        $notification->presentViewData['body']['comment'] = $commentReference;
        $this->assertStringContainsString('citations' . urlencode('/') . $reference->id, $service->getLink($notification));

        // test organisation activate all redirect and assessment item response comment
        $organisation = Organisation::factory()->create();
        $aiResponse = AssessmentItemResponse::factory()->create();
        $comment = Comment::factory()->for($organisation)->create(['commentable_type' => 'assessment_item_response', 'commentable_id' => $aiResponse->id]);
        $notification->presentViewData['body']['comment'] = $comment;
        $link = $service->getLink($notification);
        $this->assertStringContainsString('/normas/activate/all/' . $organisation->id, $link);
        $this->assertStringContainsString('assess' . urlencode('/') . 'item' . urlencode('/') . $aiResponse->assessment_item_id, $service->getLink($notification));

        $file = File::factory()->create();
        $comment = Comment::factory()->for($norma)->create(['commentable_type' => 'file', 'commentable_id' => $file->id]);
        $notification->presentViewData['body']['comment'] = $comment;
        $this->assertStringContainsString('files' . urlencode('/') . $file->id, $service->getLink($notification));

        $norma2 = Norma::factory()->create();
        $comment = Comment::factory()->for($norma)->create(['commentable_type' => 'place', 'commentable_id' => $norma2->id]);
        $notification->presentViewData['body']['comment'] = $comment;
        $this->assertStringContainsString('redirect=' . urlencode('/'), $service->getLink($notification));

        $notification->presentViewData['body']['comment'] = null;
        $this->assertTrue($service->getLink($notification) === '');

        $comment = Comment::factory()->for($norma)->create(['commentable_type' => 'place', 'commentable_id' => 0]);
        $notification->presentViewData['body']['comment'] = $comment;
        $this->assertTrue($service->getLink($notification) === '');

        $notification = Notification::factory()->create(['data' => ['body' => ['type' => 0]]]);
        $this->assertTrue($service->getLink($notification) === '');

        $commentParent = Comment::factory()->for($norma)->create(['commentable_type' => 'file', 'commentable_id' => $file->id]);
        $comment = Comment::factory()->for($norma)->create(['commentable_type' => 'comment', 'commentable_id' => $commentParent->id]);
        $notification = Notification::factory()->create(['data' => ['body' => ['type' => NotificationType::reply()->value]]]);
        $notification->presentViewData['body']['comment'] = $comment;
        $this->assertStringContainsString('files' . urlencode('/') . $file->id, $service->getLink($notification));
    }
}
