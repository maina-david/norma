<?php

namespace App\Http\Requests\Actions;

use App\Enums\Tasks\Frequency;
use App\Enums\Tasks\WeekDay;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecurringTaskRequest extends FormRequest
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
            'startDate' => ['required', 'date', 'after_or_equal:today'],
            'frequencyNumber' => ['required', 'integer', 'min:1'],
            'frequencyUnit' => ['required', 'integer', Rule::enum(Frequency::class)],
            'monthSelection' => ['nullable', 'string', 'required_if:frequencyUnit,' . Frequency::MONTHLY->value, 'in:dayOfMonth,weekOfMonth'],
            'monthDay' => ['nullable', 'required_if:monthSelection,dayOfMonth', 'integer', 'min:1', 'max:31'],
            'weekDayName' => ['nullable', 'string', 'required_if:monthSelection,weekOfMonth', Rule::enum(WeekDay::class)],
            'endCondition' => ['required', 'string', 'in:never,onDate,afterOccurrences'],
            'endDate' => ['nullable', 'required_if:endCondition,onDate', 'date', 'after:today'],
            'occurrences' => ['nullable', 'required_if:endCondition,afterOccurrences', 'integer', 'min:1'],
            'selectedDays' => ['array'],
            'selectedDays.*' => ['nullable', 'string', Rule::enum(WeekDay::class)],
        ];
    }
}
