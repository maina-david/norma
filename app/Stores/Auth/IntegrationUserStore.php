<?php

namespace App\Stores\Auth;

use App\Enums\Auth\LifecycleStage;
use App\Enums\Auth\UserType;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Throwable;

class IntegrationUserStore
{
    /**
     * Create a new user for the given integration.
     * Also attempts to connect the user to the given norma streams.
     *
     * @param array<string, mixed> $mappedUserData ['email' => '', 'fname' => '', 'sname' => '', 'phone_mobile' => '', 'integration_id' => '']
     * @param string               $provider
     *
     * @return User
     */
    public function createOrUpdateForIntegration(array $mappedUserData, string $provider): User
    {
        $providerUser = User::where('email', $mappedUserData['email']); // ->where('auth_provider', $provider);

        if ($providerUser->exists()) {
            /** @var User */
            $user = $providerUser->first();

            $this->updateIfChangedForIntegration($mappedUserData, $user);
        } else {
            /** @var Role */
            $userRole = Role::where('title', 'My Users')->where('app', 'my')->first();
            /** @var User */
            $user = User::create([
                'fname' => $mappedUserData['fname'] ?? '',
                'sname' => $mappedUserData['sname'] ?? '',
                'email' => $mappedUserData['email'],
                'password' => bcrypt(Str::random(12)),
                'phone_mobile' => $mappedUserData['phone_mobile'] ?? null,
                'integration_id' => $mappedUserData['integration_id'] ?? null,
                'auth_provider' => $provider,
                'lifecycle_stage' => LifecycleStage::active()->value,
                'user_type' => UserType::customer()->value,
                'active' => true,
                'role_id' => $userRole->id,
            ]);
            $user->roles()->attach($userRole->id);
        }

        if (isset($mappedUserData['organisations'])) {
            $this->syncOrganisations($user, $mappedUserData['organisations'], $provider);
        }

        if (isset($mappedUserData['normas'])) {
            $this->syncNormas($user, $mappedUserData['normas'], $provider);
        }

        return $user;
    }

    /**
     * @param array<string, mixed> $mappedUserData ['email' => '', 'fname' => '', 'sname' => '', 'phone_mobile' => '', 'integration_id' => '']
     * @param User                 $user
     *
     * @return User
     */
    public function updateIfChangedForIntegration(array $mappedUserData, User $user): User
    {
        $toUpdate = array_merge(
            !empty($mappedUserData['fname']) ? ['fname' => $mappedUserData['fname']] : [],
            !empty($mappedUserData['sname']) ? ['sname' => $mappedUserData['sname']] : [],
            !empty($mappedUserData['phone_mobile']) ? ['phone_mobile' => $mappedUserData['phone_mobile']] : [],
            !empty($mappedUserData['integration_id']) ? ['integration_id' => $mappedUserData['integration_id']] : [],
        );

        if (count($toUpdate) === 0) {
            // @codeCoverageIgnoreStart
            return $user;
            // @codeCoverageIgnoreEnd
        }

        $user->update($toUpdate);

        return $user;
    }

    /**
     * @param User       $user
     * @param array<int> $normaIds array of norma ids
     *
     * @throws AuthorizationException
     *
     * @return void
     */
    private function syncNormas(User $user, array $normaIds, string $provider): void
    {
        $partnerId = config('services.sso.' . $provider . '.partner_id');
        if (!$partnerId) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        /** @var Collection<Norma> */
        $normaIds = Norma::whereKey($normaIds)
            ->whereHas('organisation', function ($q) use ($partnerId) {
                $q->where('partner_id', $partnerId);
            })->get('id');

        // first attempt to sync, as norma access might have been removed,
        // in that case we also need to remove access first
        $this->syncNormasByPartner($user, $normaIds, (int) $partnerId);

        if (count($normaIds) === 0) {
            /** @var string */
            $error = __('auth.error_no_norma_access');
            throw new AuthorizationException($error);
        }
    }

    /**
     * @param User       $user
     * @param array<int> $organisations array of org ids
     * @param string     $provider
     *
     * @throws AuthorizationException
     *
     * @return void
     */
    private function syncOrganisations(User $user, array $organisations, string $provider): void
    {
        $partnerId = config('services.sso.' . $provider . '.partner_id');
        if (!$partnerId) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        /** @var Collection<Organisation> */
        $organisations = Organisation::where('partner_id', $partnerId)
            ->whereKey($organisations)
            ->get(['id', 'partner_id']);

        // first attempt to sync, as org access might have been removed,
        // in that case we also need to remove access first
        $this->syncOrganisationsByPartner($user, $organisations, (int) $partnerId);

        if (count($organisations) === 0) {
            /** @var string */
            $error = __('auth.error_unable_to_find_organisaton');
            throw new AuthorizationException($error);
        }
    }

    /**
     * Syncs the given normas to the user... non existing normas will be detached.
     *
     * @param User               $user,
     * @param Collection<Norma> $normas,
     * @param int                $partnerId
     *
     * @return void
     **/
    public function syncNormasByPartner(
        User $user,
        Collection $normas,
        int $partnerId
    ): void {
        $partnerNormas = $user->normas()->whereHas('organisation', function ($q) use ($partnerId) {
            $q->where('partner_id', $partnerId);
        })->get();

        foreach ($partnerNormas as $pl) {
            if (!$normas->contains($pl)) {
                $user->normas()->detach($pl);
            }
        }
        foreach ($normas as $norma) {
            try {
                $user->normas()->attach($norma);
                // @codeCoverageIgnoreStart
            } catch (Throwable $th) {
                // throw $th;
            }
            // @codeCoverageIgnoreEnd
        }

        // event(new UserUpdated($user));
    }

    /**
     * Syncs the given organisations to the user... non existing organisations will be detached.
     *
     * @param User                     $user,
     * @param Collection<Organisation> $organisations,
     * @param int                      $partnerId
     *
     * @return void
     **/
    public function syncOrganisationsByPartner(
        User $user,
        Collection $organisations,
        int $partnerId
    ): void {
        $partnerOrgs = $user->organisations()->where('partner_id', $partnerId)->get();

        foreach ($partnerOrgs as $partnerOrg) {
            if (!$organisations->contains($partnerOrg)) {
                $user->organisations()->detach($partnerOrg);
            }
        }

        foreach ($organisations as $org) {
            if ($org->partner_id !== $partnerId) {
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }

            try {
                $user->organisations()->attach($org);
                // @codeCoverageIgnoreStart
            } catch (Throwable $th) {
                // throw $th;
            }
            // @codeCoverageIgnoreEnd
        }

        // event(new UserUpdated($user));
    }
}
