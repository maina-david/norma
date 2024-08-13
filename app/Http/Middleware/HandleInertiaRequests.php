<?php

namespace App\Http\Middleware;

use App\Services\Customer\ActiveLibryosManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;

/**
 * @codeCoverageIgnore
 */
class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth.user' => function () {
                /** @var \App\Models\Auth\User|null $user */
                $user = Auth::user();
                $manager = app(ActiveLibryosManager::class);

                return !$user ? null : [
                    'id' => $user->id,
                    'app_ad' => $user->isMySuperUser(),
                    'org_ad' => $user->isOrganisationAdmin($manager->getActiveOrganisation()),
                ];
            },
            'stream' => function () {
                $manager = app(ActiveLibryosManager::class);
                $org = $manager->getActiveOrganisation();
                $libryo = $manager->getActive();

                return [
                    'single' => $manager->isSingleMode(),
                    'id' => $libryo?->id,
                    'title' => $libryo?->title,
                    'org_title' => $org->title ?? null,
                    'org' => $org->id ?? null,
                    'org_modules' => $org->settings['modules'] ?? [],
                    'modules' => $libryo->settings['modules'] ?? [],
                ];
            },
        ]);
    }
}
