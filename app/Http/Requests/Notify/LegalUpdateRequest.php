<?php

namespace App\Http\Requests\Notify;

use App\Http\Requests\Traits\HasCheckboxes;
use App\Models\Auth\User;
use App\Models\Geonames\Location;
use App\Rules\Ontology\LegalDomainLocationCheck;
use App\Support\Languages;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LegalUpdateRequest extends FormRequest
{
    use HasCheckboxes;

    /** @var array<string> */
    protected array $checkboxes = ['late'];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed> $rules
     */
    public function rules(): array
    {
        /** @var User $user */
        $user = $this->user();
        $locationTable = (new Location())->getTable();

        $rules = [];

        if ($user->can('collaborate.notify.legal-update.upload-related-document')) {
            $rules['content_resource_file'] = [Rule::requiredIf($this->method() === self::METHOD_POST), 'file'];
        }

        if ($user->can('collaborate.notify.legal-update.set-release-date')) {
            $rules['release_at'] = ['nullable', 'date_format:Y-m-d'];
        }

        if ($user->canAny(['collaborate.notify.legal-update.update', 'collaborate.notify.legal-update.create'])) {
            $rules = array_merge($rules, [
                'title' => ['required', 'string', 'max:1000'],
                'title_translation' => ['nullable', 'string', 'max:1000'],
                'in_terms_of_work_id' => ['nullable', 'integer'],
                'notify_reference_id' => ['nullable', 'integer'],
                'source_id' => ['required', 'integer'],
                'language_code' => ['required', 'string', Rule::in(array_keys(Languages::$alpha3To2))],
                'publication_date' => ['nullable', 'date_format:Y-m-d'],
                'work_number' => ['nullable', 'string'],
                'publication_number' => ['nullable', 'string'],
                'publication_document_number' => ['nullable', 'string'],
                'primary_location_id' => ['required', 'exists:' . $locationTable . ',id'],
                'effective_date' => ['nullable', 'date_format:Y-m-d'],
                'domains' => [
                    'required',
                    'array',
                    new LegalDomainLocationCheck($this->get('primary_location_id')),
                ],
                'late' => ['boolean'],
            ]);
        }

        if ($user->can('collaborate.notify.legal-update.update-highlights')) {
            $rules['highlights'] = ['nullable', 'string'];
        }

        if ($user->can('collaborate.notify.legal-update.update-summary-of-highlights')) {
            $rules['update_report'] = ['nullable', 'string'];
        }

        if ($user->can('collaborate.notify.legal-update.update-changes-to-register')) {
            $rules['changes_to_register'] = ['nullable', 'string'];
        }

        return $rules;
    }
}
