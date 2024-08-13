<?php

namespace App\Rules\System;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

class ReCaptcha implements ValidationRule
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
        if (config('services.google_recaptcha.enabled')) {
            $response = Http::get('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => config('services.google_recaptcha.secret'),
                'response' => $value,
            ]);

            if (!$response->json()['success']) {
                $fail(__('validation.recaptcha'));
            }
        }
    }
}
