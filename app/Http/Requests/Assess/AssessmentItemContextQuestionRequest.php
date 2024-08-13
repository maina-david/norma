<?php

namespace App\Http\Requests\Assess;

use App\Models\Compilation\ContextQuestion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssessmentItemContextQuestionRequest extends FormRequest
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
        return [
            'context_questions' => ['required', 'array'],
            'context_questions.*' => ['numeric', Rule::exists((new ContextQuestion())->getTable(), 'id')],
        ];
    }
}
