<?php

namespace App\Http\Requests\Workflows\Wizard;

use App\Enums\Workflows\TaskStatus;
use App\Models\Arachno\Source;
use App\Models\Workflows\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SelectMonitorSourceRequest extends FormRequest
{
    /**
     * {@inheritDoc}
     */
    protected function prepareForValidation()
    {
        /** @var Source|null $source */
        $source = Source::find($this->get('source_id'));

        $this->merge([
            'title' => $source->title ?? null,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed> $rules
     */
    public function rules(): array
    {
        return [
            'source_id' => [
                'integer',
                'required',
                Rule::exists(Source::class, 'id'),
                // @codeCoverageIgnoreStart
                function ($attribute, $value, $fail) {
                    /** @var Task|null $exists */
                    $exists = Task::whereRelation('document', 'source_id', $value)
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
                            'name' => __('corpus.work.source'),
                        ]));
                    }
                },
                // @codeCoverageIgnoreEnd
            ],
            'title' => ['string'],
        ];
    }
}
