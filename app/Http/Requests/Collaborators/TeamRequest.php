<?php

namespace App\Http\Requests\Collaborators;

use App\Models\Collaborators\Team;

class TeamRequest extends TeamBankDetailsRequest
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
     * @return array<string, mixed> $rules
     */
    public function rules()
    {
        $teams = Team::class;
        $rules = parent::rules();

        $rules = [
            ...$rules,
            'currency' => ['string', 'required', 'max:255'],
            'title' => ['string', "unique:{$teams}", 'required', 'max:255'],
            'auto_pay' => ['boolean'],
            'transferwise_id' => ['numeric', 'nullable'],
        ];

        if ($this->method() === 'POST') {
            return $rules;
        }

        /** @var string $teamStr */
        $teamStr = request('team');
        $rules['title'] = "max:255|required|unique:{$teams},title," . $teamStr;

        return $rules;
    }
}
