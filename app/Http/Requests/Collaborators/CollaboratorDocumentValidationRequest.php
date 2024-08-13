<?php

namespace App\Http\Requests\Collaborators;

use App\Enums\Collaborators\DocumentType;
use App\Enums\Collaborators\IdentityDocumentType;
use App\Models\Collaborators\ProfileDocument;
use Illuminate\Foundation\Http\FormRequest;

class CollaboratorDocumentValidationRequest extends FormRequest
{
    /**
     * {@inheritDoc}
     */
    protected function prepareForValidation()
    {
        $this->merge(['restricted_visa' => $this->get('restricted_visa') == 1]);

        if (!$this->get('restricted_visa')) {
            $this->merge([
                'restriction_type' => null,
                'visa_hours' => null,
                'visa_available_hours' => null,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed> $rules
     */
    public function rules()
    {
        /** @var ProfileDocument $document */
        $document = $this->route('document');

        switch ($document->type) {
            case DocumentType::IDENTIFICATION:
                $required = $this->get('subtype') === IdentityDocumentType::PASSPORT->value ? 'required' : 'nullable';

                return [
                    'subtype' => ['required', 'string', 'max:255'],
                    'valid_from' => [$required, 'date'],
                    'valid_to' => [$required, 'date', 'after:tomorrow'],
                    'comment' => ['nullable', 'string'],
                ];
            case DocumentType::VISA:
                $required = $this->get('restricted_visa') ? 'required' : 'nullable';

                return [
                    'restricted_visa' => ['required', 'boolean'],
                    'restriction_type' => [$required, 'string', 'max: 255'],
                    'visa_hours' => [$required, 'numeric'],
                    'visa_available_hours' => [$required, 'numeric'],
                    'valid_from' => [$required, 'date'],
                    'valid_to' => [$required, 'date', 'after:tomorrow'],
                    'comment' => ['nullable', 'string'],
                ];
            default:
                return [
                    'valid_from' => ['nullable', 'date'],
                    'valid_to' => ['nullable', 'date', 'after:tomorrow'],
                    'comment' => ['nullable', 'string'],
                ];
        }
    }
}
