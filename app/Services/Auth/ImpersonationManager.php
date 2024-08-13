<?php

namespace App\Services\Auth;

use App\Models\Auth\User;
use Exception;
use Illuminate\Support\Facades\Auth;

class ImpersonationManager
{
    /**
     * @return bool
     */
    public function isImpersonating(): bool
    {
        return session()->has($this->getSessionKey());
    }

    /**
     * @return int|null
     */
    public function getImpersonatorId()
    {
        return session($this->getSessionKey(), null);
    }

    /**
     * @param User $from
     * @param User $to
     *
     * @return bool
     */
    public function take(User $from, User $to): bool
    {
        try {
            session()->put($this->getSessionKey(), $from->getKey());
            Auth::quietLogout();
            Auth::quietLogin($to);
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            unset($e);

            return false;
        }
        // @codeCoverageIgnoreEnd

        return true;
    }

    /**
     * @return bool
     */
    public function leave(): bool
    {
        if (!$this->isImpersonating()) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        try {
            $impersonator = User::find($this->getImpersonatorId());
            Auth::quietLogout();
            // @phpstan-ignore-next-line
            Auth::quietLogin($impersonator);

            $this->clear();
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            unset($e);

            return false;
        }
        // @codeCoverageIgnoreEnd

        return true;
    }

    /**
     * @return void
     */
    public function clear(): void
    {
        session()->forget($this->getSessionKey());
    }

    /**
     * @return string
     */
    public function getSessionKey()
    {
        return config('session.impersonation_key');
    }
}
