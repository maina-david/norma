<?php

namespace App\Http\Requests\Customer;

use App\Http\Requests\Traits\HasCheckboxes;
use App\Models\Customer\Libryo;
use App\Models\Customer\LibryoType;
use App\Models\Geonames\Location;
use App\Models\Ontology\Category;
use App\Services\Customer\ActiveOrganisationManager;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property int $organisation_id
 */
class LibryoRequest extends FormRequest
{
    use HasCheckboxes {
        validationData as validationDataTrait;
    }

    /** @var array<string> */
    protected array $checkboxes = ['needs_recompilation', 'compilation_in_progress', 'auto_compiled', 'demo'];

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
        $data = $this->validationDataTrait();

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
     * @return array<string, mixed> $rules
     */
    public function rules()
    {
        $rules = [
            'title' => ['string', 'required', 'max:255', Rule::unique((new Libryo())->getTable())->where(function ($query) {
                return $query->where('organisation_id', $this->organisation_id);
            })],
            'description' => ['string', 'nullable', 'max:1000'],
            'organisation_id' => ['required', 'integer'],
            'address' => ['string', 'max:255', 'nullable'],
            'geo_lat' => ['numeric', 'between:-90,90', 'nullable'],
            'geo_lng' => ['numeric', 'between:-180,180', 'nullable'],
            'integration_id' => 'nullable|max:255',
        ];

        if ($this->routeIs('*api*')) {
            $rules['organisation_id'] = ['nullable', 'integer'];
        }

        if (!$this->activeOrganisationManager->isSingleOrgMode() || userCanManageAllOrgs()) {
            $location = (new Location())->getTable();

            $rules['place_type_id'] = ['integer', 'nullable', Rule::exists(LibryoType::class, 'id')];
            $rules['economic_category_id'] = ['integer', 'nullable', Rule::exists(Category::class, 'id')];
            $rules['library_id'] = ['integer', 'nullable'];
            $rules['location_id'] = ['required', 'integer', 'nullable', "exists:{$location},id"];
            $rules['compilation_in_progress'] = ['boolean'];
            $rules['needs_recompilation'] = ['boolean'];
            $rules['auto_compiled'] = ['boolean'];
            $rules['demo'] = ['boolean'];
        }

        if ($this->method() === 'POST') {
            return $rules;
        }

        $rules['title'] = ['string', 'required', 'max:255', Rule::unique((new Libryo())->getTable())->where(function ($query) {
            // @phpstan-ignore-next-line
            $libryoId = $this->route()->parameter('libryo');

            return $query->where('organisation_id', $this->organisation_id)->where('id', '!=', $libryoId);
        })];

        return $rules;
    }
}
