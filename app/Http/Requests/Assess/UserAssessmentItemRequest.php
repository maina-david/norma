<?php

namespace App\Http\Requests\Assess;

use App\Enums\Assess\ReassessInterval;
use App\Enums\Assess\RiskRating;
use App\Http\Controllers\Traits\DecodesHashids;
use App\Models\Assess\AssessmentItem;
use App\Models\Ontology\Category;
use App\Models\Ontology\LegalDomain;
use App\Services\Customer\ActiveNormasManager;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserAssessmentItemRequest extends FormRequest
{
    use DecodesHashids;

    /**
     * {@inheritDoc}
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'frequency' => $this->get('frequency'),
            'frequency_interval' => $this->get('frequency_interval', ReassessInterval::MONTH->value),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);

        /** @var string|null $exists */
        $exists = $this->route('assess');

        return [
            'title' => [
                'string', 'required',
                Rule::unique(AssessmentItem::class, 'title')
                    ->where('organisation_id', $manager->getActiveOrganisation()->id)
                    ->ignore($exists ? $this->decodeHashId($exists, AssessmentItem::class) : null),
            ],
            'legal_domain_id' => ['nullable', 'numeric', Rule::exists(LegalDomain::class, 'id')],
            'admin_category_id' => ['nullable', 'numeric', Rule::exists(Category::class, 'id')],
            'risk_rating' => ['required', 'numeric', Rule::in(RiskRating::options())],
            'frequency' => ['nullable', 'numeric'],
            'frequency_interval' => ['nullable', Rule::in([1, 2, 3, 4])],
        ];
    }
}
