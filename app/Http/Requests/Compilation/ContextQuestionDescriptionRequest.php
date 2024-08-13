<?php

namespace App\Http\Requests\Compilation;

use App\Models\Compilation\ContextQuestion;
use App\Models\Geonames\Location;
use Illuminate\Foundation\Http\FormRequest;

class ContextQuestionDescriptionRequest extends FormRequest
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
     * @return array<string,mixed>
     */
    public function rules()
    {
        $cqTable = (new ContextQuestion())->getTable();
        $locationTable = (new Location())->getTable();

        return [
            'context_question_id' => ['required', 'exists:' . $cqTable . ',id'],
            'description' => ['string'],
            'location_id' => ['nullable', 'exists:' . $locationTable . ',id'],
        ];
    }
}
