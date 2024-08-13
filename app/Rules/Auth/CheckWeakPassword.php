<?php

namespace App\Rules\Auth;

use App\Models\Auth\WeakPassword;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckWeakPassword implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param string                                                               $attribute
     * @param mixed                                                                $value
     * @param Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail
     *
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        /** @var string|null $value */
        if (WeakPassword::where('password', strtolower($value ?? ''))->exists()) {
            $fail(__('validation.custom.password.check_weak_password'));
        }
    }
}
