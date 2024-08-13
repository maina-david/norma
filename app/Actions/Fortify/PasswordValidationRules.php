<?php

namespace App\Actions\Fortify;

use App\Rules\Auth\CheckWeakPassword;
use Laravel\Fortify\Rules\Password;

trait PasswordValidationRules
{
    /**
     * Get the validation rules used to validate passwords.
     *
     * @return array<int, mixed>
     */
    protected function passwordRules(): array
    {
        return [
            'required',
            'string',
            (new Password())->requireSpecialCharacter()->requireUppercase()->length(12),
            'confirmed',
            new CheckWeakPassword(),
        ];
    }
}
