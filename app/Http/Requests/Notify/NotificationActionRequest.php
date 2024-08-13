<?php

namespace App\Http\Requests\Notify;

use App\Models\Notify\NotificationAction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NotificationActionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, \Illuminate\Contracts\Validation\Rule|string>>
     */
    public function rules(): array
    {
        return [
            'text' => ['required', 'string', Rule::unique(NotificationAction::class)->ignore($this->route('notification_action'))],
        ];
    }
}
