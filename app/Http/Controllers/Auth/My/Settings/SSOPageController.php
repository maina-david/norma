<?php

namespace App\Http\Controllers\Auth\My\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\IdentityProviderRequest;
use App\Models\Auth\IdentityProvider;
use App\Models\Customer\Organisation;
use App\Services\Customer\ActiveOrganisationManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SSOPageController extends Controller
{
    /**
     * Get the active organisation.
     *
     * @return \App\Models\Customer\Organisation
     */
    protected function getOrganisation(): Organisation
    {
        $organisation = app(ActiveOrganisationManager::class)->getActive();
        abort_unless($organisation && $organisation->hasSSOModule(), 404);

        /** @var \App\Models\Customer\Organisation */
        return $organisation;
    }

    /**
     * Get the page details.
     *
     * @return \Illuminate\View\View
     */
    public function edit(): View
    {
        $organisation = $this->getOrganisation();

        $organisation->setSlug()->save();

        $provider = IdentityProvider::where('organisation_id', $organisation->id)->with(['team'])->first();

        /** @var View */
        return view('pages.auth.my.sso.settings.index', [
            'organisation' => $organisation,
            'provider' => $provider,
        ]);
    }

    /**
     * @param \App\Http\Requests\Auth\IdentityProviderRequest $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(IdentityProviderRequest $request): RedirectResponse
    {
        $organisation = $this->getOrganisation();
        /** @var IdentityProvider $provider */
        $provider = IdentityProvider::where('organisation_id', $organisation->id)->firstOrNew();
        $provider->fill([
            ...$request->validated(),
            'organisation_id' => $organisation->id,
        ]);
        $provider->save();

        $this->notifyGeneralSuccess();

        return redirect()->route('my.settings.sso.edit');
    }
}
