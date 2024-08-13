<?php

namespace App\Models\Tasks;

use App\Enums\Tasks\TaskActivityType;
use App\Models\AbstractModel;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Models\Storage\My\File;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property \App\Models\Auth\User|null $user
 *
 * @mixin IdeHelperTaskActivity
 */
class TaskActivity extends AbstractModel
{
    /** @var string */
    protected $table = 'task_activities';

    /** @var array<string, string> */
    protected $casts = [
        'details' => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | BOOT METHOD
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Model Accessors
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Model Mutators
    |--------------------------------------------------------------------------

    /*
    |--------------------------------------------------------------------------
    | Model Relations
    |--------------------------------------------------------------------------
    */

    /**
     * @return BelongsTo
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo
     */
    public function libryo(): BelongsTo
    {
        return $this->belongsTo(Libryo::class, 'place_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Model Methods
    |--------------------------------------------------------------------------
    */

    /**
     * @return string
     **/
    public function toText(): string
    {
        /** @var string */
        $unknown = __('auth.user.unknown_user');
        $name = $this->user->full_name ?? $unknown;
        switch ($this->activity_type) {
            case TaskActivityType::statusChange()->value:
                /** @var string */
                $str = __('tasks.changed_task_status', [
                    'name' => $name,
                    'from' => is_null($this->details) || !isset($this->details['from_status']) ? __('interface.unknown') : __('tasks.task.status.' . $this->details['from_status']),
                    'to' => is_null($this->details) || !isset($this->details['to_status']) ? __('interface.unknown') : __('tasks.task.status.' . $this->details['to_status']),
                ]);

                break;
            case TaskActivityType::madeComment()->value:
                /** @var string */
                $str = __('comments.made_comment', [
                    'name' => $name,
                ]);

                break;
            case TaskActivityType::documentUpload()->value:
                /** @var string|int|null */
                $fileId = $this->details['file_id'] ?? null;
                /** @var File|null */
                $document = File::find($fileId);
                /** @var string */
                $str = __('storage.uploaded_document', [
                    'name' => $name,
                    'file' => !is_null($document) ? $document->title : __('storage.file.deleted_file'),
                ]);

                break;
            case TaskActivityType::assigneeChange()->value:
                /** @var string|int|null */
                $from = $this->details['from_user_id'] ?? null;
                /** @var string|int|null */
                $to = $this->details['to_user_id'] ?? null;
                /** @var User|null */
                $fromUser = User::find($from);
                /** @var User|null */
                $toUser = User::find($to);

                /** @var string */
                $str = __('tasks.assignee_changed', [
                    'name' => $name,
                    'from' => !is_null($fromUser) ? $fromUser->full_name : '',
                    'to' => !is_null($toUser) ? $toUser->full_name : '',
                ]);

                break;
            case TaskActivityType::watchTask()->value:
                $watching = $this->details['watching'] ?? null;

                /** @var string */
                $str = $watching ? __('tasks.started_watching', [
                    'name' => $name,
                ]) : __('tasks.stopped_watching', [
                    'name' => $name,
                ]);

                break;
            case TaskActivityType::dueDateChanged()->value:
                /** @var string|null */
                $from = $this->details['from_due_on'] ?? null;
                /** @var string|null */
                $to = $this->details['to_due_on'] ?? null;
                $from = $from ? Carbon::parse($from)->toFormattedDateString() : __('tasks.no_due_date');
                $to = $to ? Carbon::parse($to)->toFormattedDateString() : __('tasks.no_due_date');

                /** @var string */
                $str = __('tasks.changed_due_date', [
                    'name' => $name,
                    'from' => $from,
                    'to' => $to,
                ]);

                break;
            case TaskActivityType::setReminder()->value:
                /** @var string */
                $str = __('tasks.set_reminder', [
                    'name' => $name,
                ]);

                break;
            case TaskActivityType::priorityChanged()->value:
                /** @var string|int|null */
                $from = $this->details['from_priority'] ?? null;
                /** @var string|int|null */
                $to = $this->details['to_priority'] ?? null;
                /** @var string */
                $str = __('tasks.priority_changed', [
                    'name' => $name,
                    'from' => $from ? __('tasks.task.priority.' . $from) : __('tasks.no_priority_set'),
                    'to' => $to ? __('tasks.task.priority.' . $to) : __('tasks.no_priority_set'),
                ]);

                break;
            case TaskActivityType::titleChanged()->value:
                /** @var string|int|null */
                $from = $this->details['from_title'] ?? null;
                /** @var string|int|null */
                $to = $this->details['to_title'] ?? null;

                /** @var string */
                $str = __('tasks.title_changed', [
                    'name' => $name,
                    'from' => $from ?? '',
                    'to' => $to ?? '',
                ]);

                break;
            default:
                /** @var string */
                $str = '';

                break;
        }

        return $str;
    }
}
