<?php

namespace App\Http\Requests\Workflows\Wizard;

use App\Enums\Workflows\TaskStatus;
use App\Enums\Workflows\WizardStep;
use App\Models\Corpus\WorkExpression;
use App\Models\Workflows\Board;
use App\Models\Workflows\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SelectWorkRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed> $rules
     */
    public function rules(): array
    {
        /** @var int $board */
        $board = $this->route('board');
        /** @var Board $board */
        $board = Board::cached($board);

        return [
            'work_expression_id' => [
                'integer',
                Rule::requiredIf(!in_array(WizardStep::SELECT_WORK->value, $board->optional_wizard_steps ?? [])),
                Rule::exists(WorkExpression::class, 'id'),
                // @codeCoverageIgnoreStart
                function ($attribute, $value, $fail) {
                    /** @var WorkExpression|null $expression */
                    $expression = WorkExpression::find($value);

                    /** @var Task|null $exists */
                    $exists = Task::whereRelation('document', 'work_id', $expression->work_id ?? 0)
                        ->whereIn('task_status', [
                            TaskStatus::todo()->value,
                            TaskStatus::inProgress()->value,
                            TaskStatus::inReview()->value,
                            TaskStatus::pending()->value,
                        ])
                        ->first();
                    if ($exists) {
                        $fail(__('workflows.task.validations.existing_tasks', [
                            'target' => route('collaborate.tasks.show', $exists->id),
                            'number' => $exists->id,
                            'name' => __('corpus.work.index_title'),
                        ]));
                    }
                },
                // @codeCoverageIgnoreEnd
            ],
        ];
    }
}
