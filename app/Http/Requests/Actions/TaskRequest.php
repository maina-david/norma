<?php

namespace App\Http\Requests\Actions;

use App\Enums\Tasks\TaskRepeatInterval;
use App\Http\Requests\Traits\HasCheckboxes;
use App\Models\Auth\User;
use App\Services\Customer\ActiveNormasManager;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaskRequest extends FormRequest
{
    use HasCheckboxes;

    /** @var array<string> */
    protected array $checkboxes = ['reminder'];

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
        $rules = [
            'title' => ['string', 'max:255'],
            'description' => ['string', 'nullable', 'max:5000'],
            'task_status' => ['numeric'],
            'action_area_id' => ['numeric', 'nullable'],
            'reference_content_extract_id' => ['numeric', 'nullable'],
            'priority' => ['numeric', 'nullable'],
            'impact' => ['numeric', 'nullable'],
            'assigned_to_id' => ['numeric', 'nullable'],
            'due_on' => ['date', 'nullable'],
            'task_project_id' => ['numeric', 'nullable'],
            'followers' => ['array', 'nullable'],
            'followers.*' => ['numeric', Rule::exists(User::class)],
            'frequency' => ['nullable', 'numeric'],
            'frequency_interval' => ['nullable', Rule::enum(TaskRepeatInterval::class)],
        ];

        if ($this->method() === 'POST') {
            $normaRequired = app(ActiveNormasManager::class)->isSingleMode() ? [] : ['required'];

            $rules['taskable_type'] = ['string', 'required', 'max:255'];
            $rules['taskable_id'] = ['numeric', 'required'];
            $rules['norma_id'] = ['numeric', ...$normaRequired];
            $rules['reminder'] = ['boolean'];
            $rules['remind_whom'] = ['string', 'nullable', 'max:35'];
            $rules['remind_on_date'] = ['date_format:Y-m-d', 'nullable'];
            $rules['remind_on_time'] = ['regex:/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', 'nullable'];
            $rules['title'][] = ['required'];
            $rules['task_status'][] = ['required'];

            return $rules;
        }

        return collect($rules)->filter(fn ($val, $key) => $this->has($key))->toArray();
    }
}
