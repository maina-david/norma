<?php

namespace App\Http\Requests\Workflows\Wizard;

use App\Enums\Workflows\TaskStatus;
use App\Http\Requests\Notify\LegalUpdateRequest;
use App\Models\Notify\LegalUpdate;
use App\Models\Workflows\Task;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class SelectLegalUpdateRequest extends LegalUpdateRequest
{
    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // If we are from the docs for updates page, fetch the legal update fields.
        if ($this->has('legal_update_id') && !$this->has('title')) {
            /** @var LegalUpdate|null $update */
            $update = LegalUpdate::whereKey($this->get('legal_update_id'))->with(['legalDomains:id'])->first();

            if ($update) {
                $domains = $update->legalDomains->pluck('id')->toArray();
                $update = $update->toArray();
                $update['domains'] = $domains;
                $update['publication_date'] = $update['publication_date'] ? Carbon::parse($update['publication_date'])->format('Y-m-d') : null;
                $update['effective_date'] = $update['effective_date'] ? Carbon::parse($update['publication_date'])->format('Y-m-d') : null;
                $this->merge($update);
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed> $rules
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'content_resource_file' => [],
            'language_code' => [Rule::requiredIf(fn () => $this->has('from_form'))],
            'primary_location_id' => [Rule::requiredIf(fn () => $this->has('from_form'))],
            'domains' => [Rule::requiredIf(fn () => $this->has('from_form')), 'array'],
            'legal_update_id' => [
                'integer',
                'required',
                Rule::exists(LegalUpdate::class, 'id'),
                // @codeCoverageIgnoreStart
                function ($attribute, $value, $fail) {
                    /** @var Task|null $exists */
                    $exists = Task::whereRelation('document', 'legal_update_id', $value)
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
                            'name' => __('corpus.doc.legal_update'),
                        ]));
                    }
                },
                // @codeCoverageIgnoreEnd
            ],
        ];
    }
}
