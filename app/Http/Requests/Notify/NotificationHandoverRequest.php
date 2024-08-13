<?php

namespace App\Http\Requests\Notify;

use App\Models\Notify\NotificationHandover;
use App\Models\Workflows\Board;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NotificationHandoverRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<\Illuminate\Contracts\Validation\Rule|string>>
     */
    public function rules(): array
    {
        return [
            'text' => ['required', 'string', Rule::unique(NotificationHandover::class)->ignore($this->route('notification_handover'))],
            'board_id' => ['nullable', Rule::exists(Board::class, 'id')],
        ];
    }
}
