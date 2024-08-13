<?php

namespace App\Services\Partners;

use App\Models\Auth\User;
use App\Models\Customer\Organisation;
use App\Models\Partners\WhiteLabel;

/**
 * Helps format data for the search.
 **/
class WhitelabelsForUser
{
    /**
     * Returns the default whitelabel site for the user.
     *
     * @return WhiteLabel|null
     **/
    public function getDefaultForUser(User $user): ?WhiteLabel
    {
        $user->load(['organisations.whitelabel']);
        if (!$organisation = $user->organisations->first()) {
            return null;
        }
        /** @var Organisation $organisation */

        return $organisation->whitelabel;
    }

    /**
     * Returns the default whitelabel site for the user.
     *
     * @return string
     **/
    public function getBaseUrlForUser(User $user): string
    {
        $whitelabel = $this->getDefaultForUser($user);

        $baseClientUrl = config('app.url');

        if ($whitelabel) {
            $replaceStr = 'my.';
            $baseClientUrl = str_replace($replaceStr, $whitelabel->shortname . '.', $baseClientUrl);
        }

        return $baseClientUrl;
    }
}
