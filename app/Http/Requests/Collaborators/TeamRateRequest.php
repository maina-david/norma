<?php

namespace App\Http\Requests\Collaborators;

use App\Models\Collaborators\Team;
use App\Models\Collaborators\TeamRate;
use App\Models\Workflows\TaskType;
use Illuminate\Foundation\Http\FormRequest;

class TeamRateRequest extends FormRequest
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
        return [
            'team_id' => sprintf('exists:%s,id', Team::class),
            'for_task_type_id' => [sprintf('exists:%s,id', TaskType::class), function ($attribute, $value, $fail) {
                $teamId = $this->route('team');
                $teamRateId = $this->route('team_rate');
                $taskTypeExists = TeamRate::where('team_id', $teamId)
                    ->where('for_task_type_id', $value)
                    ->when($teamRateId, fn ($q) => $q->where('id', '!=', $teamRateId))
                    ->exists();
                if ($taskTypeExists) {
                    /** @var string */
                    $msg = __('collaborators.team_rate.error_task_type_exists');
                    $fail($msg);
                }
            }],
            'rate_per_unit' => ['required', 'numeric'],
            'unit_type_singular' => ['required', 'string'],
            'unit_type_plural' => ['required', 'string'],
        ];
    }
}
