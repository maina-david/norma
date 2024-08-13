<?php

namespace App\Http\Requests\Requirements;

use App\Enums\Requirements\ConsequenceAmountPrefix;
use App\Enums\Requirements\ConsequenceAmountType;
use App\Enums\Requirements\ConsequencePeriod;
use App\Support\Currencies;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConsequenceRequest extends FormRequest
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
            'consequence_type' => ['required', 'integer'],
            'consequence_other_detail' => ['nullable', 'string', 'max:255'],
            'amount_prefix' => ['nullable', 'numeric', Rule::in(ConsequenceAmountPrefix::options())],
            'amount' => ['nullable', 'numeric'],
            'amount_type' => ['nullable', 'numeric', Rule::in(ConsequenceAmountType::options())],
            'currency' => ['nullable', 'string', Rule::in(Currencies::codes())],
            'sentence_period' => ['nullable', 'numeric'],
            'sentence_period_type' => ['nullable', 'numeric', Rule::in(ConsequencePeriod::options())],
            'per_day' => ['required', 'boolean'],
            'severity' => ['nullable', 'numeric'],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge(['per_day' => $this->has('per_day')]);
    }
}
