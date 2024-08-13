<?php

namespace App\Console\Commands\Once;

use App\Enums\Notifications\NotificationType;
use App\Models\Notify\Notification;
use Illuminate\Console\Command;

/**
 * @codeCoverageIgnore
 */
class BackfillNotificationType extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'once:back-fill-notification-type';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix for missing notification types.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $map = [
            'App\Notifications\Tasks\TaskDueNotification' => NotificationType::taskDue()->value,
            'App\Notifications\Notify\ReminderNotification' => NotificationType::reminder()->value,
            'App\Notifications\Comments\MentionedNotification' => NotificationType::mention()->value,
            'App\Notifications\Tasks\TaskAssignedNotification' => NotificationType::taskAssigned()->value,
            'App\Notifications\Tasks\TaskAssigneeChangedNotification' => NotificationType::taskAssigneeChanged()->value,
            'App\Notifications\Tasks\TaskDueDateChangedNotification' => NotificationType::taskDueDateChanged()->value,
            'App\Notifications\Tasks\TaskStatusChangedNotification' => NotificationType::taskStatusChanged()->value,
            'App\Notifications\Tasks\TaskPriorityChangedNotification' => NotificationType::taskPriorityChanged()->value,
            'App\Notifications\Tasks\TaskCompletedNotification' => NotificationType::taskCompleted()->value,
            'App\Notifications\Tasks\TaskTitleChangedNotification' => NotificationType::taskTitleChanged()->value,
        ];

        Notification::where('data->body->type', '')
            ->orderBy('id')
            ->cursor()
            ->each(function (Notification $notification) use ($map) {
                $data = $notification->data;
                $data['body']['type'] = $map[$notification->type] ?? '';
                $notification->data = $data;
                $notification->save();

                $this->info("Updated notification {$notification->id}");
            });

        return 0;
    }
}
