<?php

namespace App\Http\Requests\Customer;

use App\Services\Customer\ActiveOrganisationManager;
use Illuminate\Foundation\Http\FormRequest;

class TeamRequest extends FormRequest
{
    public function __construct(protected ActiveOrganisationManager $activeOrganisationManager)
    {
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Overrides FormRequest validationData: Get data to be validated from the request.
     *
     * @return array<string, mixed>
     */
    public function validationData(): array
    {
        $data = parent::validationData();

        // when in single org mode, we won't show the organisation selector - so need to set it
        if ($this->activeOrganisationManager->isSingleOrgMode()) {
            $organisation = $this->activeOrganisationManager->getActive();
            $data['organisation_id'] = $organisation?->id;
        }

        return $data;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $rules = [
            'title' => ['string', 'required', 'max:255'],
        ];

        if (!$this->activeOrganisationManager->isSingleOrgMode()) {
            $rules['organisation_id'] = ['integer', 'required'];
        } else {
            $rules['organisation_id'] = ['integer'];
        }

        return $rules;
    }
}
