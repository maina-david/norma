<?php

namespace App\Http\Requests\Geonames;

use App\Models\Geonames\Location;
use Illuminate\Foundation\Http\FormRequest;

class LocationSearchRequest extends FormRequest
{
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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $table = (new Location())->getTable();

        return [
            'country' => ['required', "exists:{$table},slug"],
            'level' => ['required', 'numeric'],
            'name' => ['required', 'string'],
            'level_2' => ['string'],
            'level_3' => ['string'],
        ];
    }
}
