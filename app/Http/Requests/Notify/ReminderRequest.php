<?php

namespace App\Http\Requests\Notify;

use Illuminate\Foundation\Http\FormRequest;

class ReminderRequest extends FormRequest
{
    /**
     * {@inheritDoc}
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'notification_config' => collect($this->get('notification_config', []))->map(fn ($item) => (int) $item)->all(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['string', 'nullable', 'max:255'],
            'description' => ['string', 'nullable', 'max:1000'],
            'remind_whom' => ['string', 'nullable', 'max:35'],
            'notification_config' => ['nullable', 'array'],
            'notification_config.*' => ['numeric'],
            'remind_on_date' => ['required', 'date_format:Y-m-d', 'nullable'],
            'remind_on_time' => ['regex:/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', 'nullable'],
        ];
    }
}
