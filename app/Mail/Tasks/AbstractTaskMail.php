<?php

namespace App\Mail\Tasks;

use App\Enums\Tasks\TaskType;
use App\Mail\Auth\AbstractUserMailable;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Auth\User;
use App\Models\Tasks\Task;
use App\Services\Partners\WhitelabelsForUser;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

abstract class AbstractTaskMail extends AbstractUserMailable
{
    use Queueable;
    use SerializesModels;

    protected int $changedById;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, protected Task $task)
    {
        parent::__construct($user);
        $this->setCommonViewData();
    }

    /**
     * Generate a url to the task item.
     *
     * @return string|null
     */
    protected function generateItemUrl(): ?string
    {
        if (empty($this->task->taskable)) {
            return null;
        }

        $whiteLabels = app(WhitelabelsForUser::class);
        if (TaskType::assess()->is($this->task->taskable_type)) {
            /** @var AssessmentItemResponse */
            $aiResponse = $this->task->taskable;

            return $whiteLabels->getBaseUrlForUser($this->user) . route('my.assess.assessment-item.show', ['assessmentItem' => $aiResponse->assessment_item_id], false);
        }
        if (TaskType::file()->is($this->task->taskable_type)) {
            return $whiteLabels->getBaseUrlForUser($this->user) . route('my.drives.files.show', ['file' => $this->task->taskable_id], false);
        }
        if (TaskType::requirements()->is($this->task->taskable_type)) {
            return $whiteLabels->getBaseUrlForUser($this->user) . route('my.corpus.references.show', ['reference' => $this->task->taskable_id], false);
        }

        return null;
    }

    /**
     * @return string|null
     */
    protected function getItemTitle(): ?string
    {
        if (TaskType::assess()->is($this->task->taskable_type)) {
            return $this->task->assessmentItemResponse?->assessmentItem?->toDescription() ?? '';
        }
        if (TaskType::file()->is($this->task->taskable_type)) {
            /** @var string */
            $deleted = __('tasks.deleted_file');

            return $this->task->taskableFile?->title ?? $deleted;
        }
        if (TaskType::requirements()->is($this->task->taskable_type)) {
            if (!$this->task->reference?->citation) {
                // @codeCoverageIgnoreStart
                return '';
                // @codeCoverageIgnoreEnd
            }

            return $this->task->reference->refPlainText?->plain_text;
        }

        return null;
    }

    /**
     * @return void
     */
    protected function setCommonViewData(): void
    {
        $this->with([
            'url' => $this->generateItemUrl(),
            'item' => $this->getItemTitle(),
            'due_on' => $this->task->due_on ? $this->formatDate($this->task->due_on) : null,
            'task' => $this->task,
            'appName' => $this->getAppName(),
        ]);
    }

    /**
     * @return string|null
     */
    protected function getChangedBy(): ?string
    {
        /** @var User|null */
        $changedBy = User::find($this->changedById);

        /** @var User|null */
        $changedBy = $this->user->id === $this->changedById
            // @codeCoverageIgnoreStart
            ? null
            // @codeCoverageIgnoreEnd
            : $changedBy?->full_name;

        return $changedBy;
    }
}
