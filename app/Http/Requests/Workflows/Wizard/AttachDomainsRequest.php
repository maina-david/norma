<?php

namespace App\Http\Requests\Workflows\Wizard;

use App\Enums\Workflows\WizardStep;
use App\Models\Workflows\Board;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttachDomainsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed> $rules
     */
    public function rules(): array
    {
        /** @var int $board */
        $board = $this->route('board');
        /** @var Board $board */
        $board = Board::cached($board);

        return [
            'legal_domains' => [
                Rule::requiredIf(!in_array(WizardStep::ATTACH_DOMAINS->value, $board->optional_wizard_steps ?? [])),
            ],
        ];
    }
}
