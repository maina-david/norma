<?php

namespace App\Http\Requests\Notify;

use App\Enums\Notify\NotificationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCandidateNotificationStatusRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed> $rules
     */
    public function rules(): array
    {
        return [
            'notification_status' => [
                'required', 'integer',
                Rule::in(collect(NotificationStatus::forSelector())->pluck('value')->toArray()),
            ],
            'notification_status_reason' => [
                'string', 'required',
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getRedirectUrl()
    {
        return route('collaborate.docs-for-update.notification-status.create', ['doc' => $this->route('doc')]);
    }
}
