<?php

namespace App\Http\Requests\Auth;

use App\Rules\System\ReCaptcha;
use Laravel\Fortify\LoginRateLimiter;

class LoginRequest extends \Laravel\Fortify\Http\Requests\LoginRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $limiter = app(LoginRateLimiter::class);
        $required = $limiter->attempts($this) == 0 ? 'nullable' : 'required';

        return [
            ...parent::rules(),
            'g-recaptcha-response' => [$required, new ReCaptcha()],
        ];
    }
}
