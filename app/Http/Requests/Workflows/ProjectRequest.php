<?php

namespace App\Http\Requests\Workflows;

use App\Enums\Workflows\ProjectRagStatus;
use App\Models\Arachno\Source;
use App\Models\Auth\User;
use App\Models\Collaborators\Group;
use App\Models\Collaborators\ProductionPod;
use App\Models\Customer\Organisation;
use App\Models\Geonames\Location;
use App\Models\Ontology\LegalDomain;
use App\Models\Workflows\Board;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectRequest extends FormRequest
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
     * @return array<string,mixed> $rules
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'context_due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'actions_due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'rag_status' => ['required', Rule::enum(ProjectRagStatus::class)],
            'organisation_id' => ['nullable', Rule::exists(Organisation::class, 'id')],
            'owner_id' => ['nullable', Rule::exists(User::class, 'id')],
            'board_id' => ['nullable', Rule::exists(Board::class, 'id')],
            'group_id' => ['required', Rule::exists(Group::class, 'id')],
            'production_pod_id' => ['nullable', Rule::exists(ProductionPod::class, 'id')],
            'location_id' => ['nullable', Rule::exists(Location::class, 'id')],
            'language_code' => ['nullable', 'string', 'max:10'],
            'domains' => ['nullable', 'array'],
            'domains.*' => Rule::exists(LegalDomain::class, 'id'),
            'sources' => ['nullable', 'array'],
            'sources.*' => Rule::exists(Source::class, 'id'),
            'tracking_specialists' => ['nullable', 'array'],
            'tracking_specialists.*' => [Rule::exists(User::class, 'id')],
        ];
    }
}
