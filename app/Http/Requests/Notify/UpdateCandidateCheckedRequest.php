<?php

namespace App\Http\Requests\Notify;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property bool $affected_available
 */
class UpdateCandidateCheckedRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed> $rules
     */
    public function rules(): array
    {
        return [
            'check_sources_status' => ['required', 'int'],
            'checked_comment' => ['required', 'string'],
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getRedirectUrl()
    {
        return route('collaborate.docs-for-update.check.create', ['doc' => $this->route('doc')]);
    }
}
