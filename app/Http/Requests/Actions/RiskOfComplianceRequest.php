<?php

namespace App\Http\Requests\Actions;

use App\Enums\Actions\RiskOfNonCompliance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RiskOfComplianceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string,mixed>
     */
    public function rules(): array
    {
        return [
            'riskOfNonCompliance' => ['required', 'integer', Rule::enum(RiskOfNonCompliance::class)],
        ];
    }
}
