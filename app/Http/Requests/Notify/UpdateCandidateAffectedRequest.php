<?php

namespace App\Http\Requests\Notify;

use App\Enums\Notify\AffectedLegislationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property bool $affected_available
 */
class UpdateCandidateAffectedRequest extends FormRequest
{
    /**
     * {@inheritDoc}
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'affected_available' => !empty($this->affected_legislation),
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
            'affected_available' => ['required', 'boolean'],
            'affected_legislation' => [Rule::requiredIf($this->affected_available), 'array'],
            'affected_legislation.*.title' => ['required', 'string', 'max:1000'],
            'affected_legislation.*.title_translation' => ['nullable', 'string', 'max:1000'],
            'affected_legislation.*.source_url' => ['required', 'string', 'max:1000'],
            'affected_legislation.*.type' => [
                'required', 'numeric',
                Rule::in(collect(AffectedLegislationType::forSelector())->pluck('value')->toArray()),
            ],
        ];
    }

    /**
     * {@inheritDoc}
     *
     * @codeCoverageIgnore
     */
    protected function getRedirectUrl()
    {
        return route('collaborate.docs-for-update.affected.create', ['doc' => $this->route('doc')]);
    }
}
