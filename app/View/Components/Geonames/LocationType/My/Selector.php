<?php

namespace App\View\Components\Geonames\LocationType\My;

use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Geonames\LocationType;
use App\Services\Customer\ActiveNormasManager;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class Selector extends Component
{
    /** @var array<string|int, mixed> */
    public array $options = [];

    /**
     * Undocumented function.
     *
     * @param string                       $name
     * @param string|int|array<int|string> $value
     * @param string                       $label
     * @param bool                         $multiple
     * @param bool                         $updates
     */
    public function __construct(
        public string $name,
        public string|int|array $value = '',
        public string $label = '',
        public bool $multiple = false,
        public bool $updates = false,
    ) {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render()
    {
        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);
        /** @var User */
        $user = Auth::user();
        $organisation = $norma = null;
        if ($manager->isSingleMode()) {
            /** @var Norma */
            $norma = $manager->getActive();
            $norma->load('location');
            $locationTypeQuery = $this->updates ? LocationType::forNormaViaUpdates($norma) : LocationType::forNorma($norma);
        } else {
            /** @var Organisation */
            $organisation = $manager->getActiveOrganisation();
            $locationTypeQuery = $this->updates ? LocationType::forOrganisationUserAccessViaUpdates($organisation, $user) : LocationType::forOrganisationUserAccess($organisation, $user);
        }

        $this->options = array_merge(
            [['label' => '--', 'value' => '']],
            $locationTypeQuery
                ->get()
//                ->sortBy(fn ($type) => $type->locations->first()->level)
                ->map(fn ($type) => ['label' => __('corpus.location_type.' . $type->adjective_key), 'value' => $type->id])
                ->sortBy('label')
                ->values()
                ->all()
        );

        /** @var View */
        return view('components.geonames.location-type.my.selector');
    }
}
