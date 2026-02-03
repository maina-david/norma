<?php

namespace App\Traits\Auth;

use App\Enums\Auth\UserType;
use App\Models\Auth\IdentityProvider;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Customer\Team;
use App\Stores\Customer\TeamUserStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

trait UsesSAMLAuthentication
{
    /**
     * Find the applicable organisation by slug.
     *
     * @param string $slug
     *
     * @return \App\Models\Customer\Organisation
     */
    protected function findOrganisationBySlug(string $slug): Organisation
    {
        /** @var Organisation $organisation */
        $organisation = Organisation::where('slug', $slug)
            ->whereRelation('identityProvider', 'enabled', true)
            ->with(['identityProvider'])
            ->firstOrFail();

        abort_unless($organisation->hasSSOModule(), 404);

        return $organisation;
    }

    /**
     * Find the identity provider for the organisation with the given slug.
     *
     * @param string $slug
     *
     * @return \App\Models\Auth\IdentityProvider
     */
    protected function findIdentityProviderBySlug(string $slug): IdentityProvider
    {
        $organisation = $this->findOrganisationBySlug($slug);

        /** @var IdentityProvider */
        return $organisation->identityProvider;
    }

    /**
     * Update the existing SAML user or create one.
     *
     * @param \App\Models\Auth\IdentityProvider $provider
     * @param \App\Models\Auth\User             $user
     * @param array<string, mixed>              $fields
     *
     * @return \App\Models\Auth\User
     */
    protected function updateSAMLUserDetails(IdentityProvider $provider, User $user, array $fields): User
    {
        $exists = (bool) $user->id;
        $user->forceFill([
            'fname' => $fields['first_name'],
            'sname' => $fields['last_name'],
            'email' => $fields['email'],
            'identity_provider_id' => $provider->id,
            'identity_provider_name_id' => $fields['name_id'],
            'user_type' => UserType::customer()->value,
        ]);

        $user->save();

        if ($exists) {
            return $user;
        }

        $this->attachOrganisationToSAMLUser($provider, $user);
        $this->attachTeamToSAMLUser($provider, $user);
        $this->attachRoleToSAMLUser($user);

        return $user;
    }

    /**
     * Attach the default team to the user.
     *
     * @param \App\Models\Auth\IdentityProvider $provider
     * @param \App\Models\Auth\User             $user
     *
     * @return void
     */
    private function attachOrganisationToSAMLUser(IdentityProvider $provider, User $user): void
    {
        try {
            $user->organisations()->attach($provider->organisation_id);
            // @codeCoverageIgnoreStart
        } catch (Throwable) {
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Attach the default team to the user.
     *
     * @param \App\Models\Auth\IdentityProvider $provider
     * @param \App\Models\Auth\User             $user
     *
     * @return void
     */
    private function attachTeamToSAMLUser(IdentityProvider $provider, User $user): void
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, Team> $teams */
        $teams = (new Team())->newCollection([$provider->team_id]);
        app(TeamUserStore::class)->attachTeams($user, $teams);

        try {
            /** @var Team $team */
            $team = Team::whereKey($provider->team_id)->first();
            /** @var Norma $norma */
            $norma = $team->normas()->active()->first();
            $user->normas()->attach($norma->id);
        } catch (Throwable) {
        }
    }

    /**
     * Attach the default role to the user.
     *
     * @param \App\Models\Auth\User $user
     *
     * @return void
     */
    private function attachRoleToSAMLUser(User $user): void
    {
        $role = Role::where('title', 'My Users')->first();

        try {
            /** @var Role $role */
            $user->roles()->attach($role->id);
        } catch (Throwable) {
        }
    }

    /**
     * Extract the email, firstname and lastname from the response.
     *
     * @param array<string, mixed> $response
     *
     * @return array<string, mixed>
     */
    protected function extractUserFieldsFromSAMLResponse(?array $response): array
    {
        $response ??= [];

        $extracted = [
            'name_id' => $response['samlNameId'] ?? null,
        ];

        foreach ($response['samlUserdata'] as $field => $value) {
            if (str_ends_with($field, 'email')) {
                $extracted['email'] = $value[0];
            }
            if (str_ends_with($field, 'first_name')) {
                $extracted['first_name'] = $value[0];
            }
            if (str_ends_with($field, 'last_name')) {
                $extracted['last_name'] = $value[0];
            }
        }

        return $extracted;
    }

    /**
     * Update the existing SAML user or create one.
     *
     * @param \App\Models\Auth\IdentityProvider $provider
     * @param array<string, mixed>              $fields
     * @param string                            $slug
     *
     * @return \App\Models\Auth\User|\Illuminate\Http\RedirectResponse
     */
    protected function updateSAMLUser(IdentityProvider $provider, array $fields, string $slug): User|RedirectResponse
    {
        $byEmail = $this->findUserByEmail($fields['email']);
        $byProvider = $this->findUserByProvider($provider, $fields['name_id']);

        // User cannot be found by email or provider,
        // or the user is found by provider but not by email,
        // or the found user by email and provider are the same
        if (
            (!$byEmail && !$byProvider)
            || ($byProvider && !$byEmail
                || ($byProvider?->id === $byEmail?->id))
        ) {
            return $this->updateSAMLUserDetails($provider, $byProvider ?? new User(), $fields);
        }

        // User exists by provider and by email, meaning two accounts have been created since the check above
        // has guaranteed that the user IDs do not match.
        if ($byEmail && $byProvider) {
            session()->flash('saml_failed_error', __('auth.saml.provider_mismatch', ['code' => 201]));

            return redirect()->route('my.saml.login.index', ['slug' => $slug]);
        }

        // User exists via password authentication since there is no identity_provider_id
        // and doesn't exist via provider.
        if ($byEmail && !$byProvider && !$byEmail->identity_provider_id) {
            $fields['norma_user_id'] = $byEmail->id;
            session()->put('saml_response', $fields);

            return redirect()->route('my.saml.conflict.create', ['slug' => $slug]);
        }

        // User exists by provider, but the provider details do not match.
        if (
            $provider->id !== ($byEmail->identity_provider_id ?? false)
            || $fields['name_id'] !== ($byEmail->identity_provider_name_id ?? false)
        ) {
            session()->flash('saml_failed_error', __('auth.saml.provider_mismatch', ['code' => 202]));

            return redirect()->route('my.saml.login.index', ['slug' => $slug]);
        }

        // @codeCoverageIgnoreStart
        session()->flash('saml_failed_error', __('auth.saml.provider_mismatch', ['code' => 203]));

        return redirect()->route('my.saml.login.index', ['slug' => $slug]);
        // @codeCoverageIgnoreEnd
    }

    /**
     * Log in the user.
     *
     * @param \App\Models\Auth\User $user
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function loginSAMLUser(User $user): RedirectResponse
    {
        Auth::login($user);

        session()->regenerate();

        return redirect()->route('my.dashboard');
    }

    /**
     * Logout the given user.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    protected function logoutSAMLUser(Request $request): void
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    /**
     * Find the user by email.
     *
     * @param string $email
     *
     * @return \App\Models\Auth\User|null
     */
    protected function findUserByEmail(string $email): ?User
    {
        /** @var User|null */
        return User::where('email', $email)->first();
    }

    /**
     * Find the user that matches the given provider.
     *
     * @param \App\Models\Auth\IdentityProvider $provider
     * @param string                            $nameId
     *
     * @return \App\Models\Auth\User|null
     */
    protected function findUserByProvider(IdentityProvider $provider, string $nameId): ?User
    {
        /** @var User|null */
        return User::where('identity_provider_id', $provider->id)
            ->where('identity_provider_name_id', $nameId)
            ->first();
    }
}
