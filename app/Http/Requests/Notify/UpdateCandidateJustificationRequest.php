<?php

namespace App\Http\Requests\Notify;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property bool $affected_available
 */
class UpdateCandidateJustificationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed> $rules
     */
    public function rules(): array
    {
        return [
            'justification_status' => ['required', Rule::in([0, 1])],
            'justification' => ['required', 'string'],
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getRedirectUrl()
    {
        return route('collaborate.docs-for-update.justification.create', ['doc' => $this->route('doc')]);
    }
}
