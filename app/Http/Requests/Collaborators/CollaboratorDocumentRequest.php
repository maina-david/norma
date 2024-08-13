<?php

namespace App\Http\Requests\Collaborators;

use App\Enums\Collaborators\DocumentArchiveOptions;
use App\Enums\Collaborators\DocumentType;
use App\Services\Storage\MimeTypeManager;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CollaboratorDocumentRequest extends FormRequest
{
    /**
     * {@inheritDoc}
     *
     * @codeCoverageIgnore
     */
    protected function getRedirectUrl()
    {
        $target = parent::getRedirectUrl();

        if (!str_contains($target, '?')) {
            $target = "{$target}?tab=documents";
        }

        return $target;
    }

    /**
     * {@inheritDoc}
     */
    protected function prepareForValidation()
    {
        if ($this->route('type') === 'contract') {
            $this->merge([
                'contract_signed' => $this->has('contract_signed'),
            ]);
        }
    }

    /**
     * @return array<int, string>
     */
    protected function requiresReason(): array
    {
        return [
            DocumentType::IDENTIFICATION->field(),
            DocumentType::VISA->field(),
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed> $rules
     */
    public function rules()
    {
        $mimes = implode(',', (new MimeTypeManager())->getAcceptedUploadMimes());
        /** @var string $field */
        $field = $this->route('type');

        $rules = [
            'reason_for_update' => [Rule::requiredIf(fn () => in_array($field, $this->requiresReason())), 'numeric'],
            $field => [
                Rule::requiredIf(fn () => $this->get('reason_for_update') != DocumentArchiveOptions::NOT_REQUIRED->value),
                'file',
                'max:51200',
                "mimetypes:{$mimes}",
            ],
        ];

        if ($field === 'contract') {
            $rules['contract_signed'] = ['boolean', 'required'];
            $rules['contract_type'] = ['integer', 'required'];
        }

        return $rules;
    }
}
