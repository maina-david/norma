<?php

namespace App\Traits\Geonames;

use App\Models\Geonames\Location;
use App\Models\Geonames\LocationType;
use App\View\Components\Geonames\LocationType\My\Selector;
use Illuminate\Contracts\View\View;
use Illuminate\View\ComponentAttributeBag;

trait UsesJurisdictionFilter
{
    /**
     * @return array<string, mixed>
     */
    public function getJurisdictionFilter(): array
    {
        return [
            'label' => __('geonames.location.jurisdiction'),
            'render' => function () {
                /** @var string $jurisdiction */
                $jurisdiction = $this->getFilterValue('jurisdiction');

                return view('components.geonames.location.location-selector', [
                    'value' => $jurisdiction,
                    'name' => 'jurisdiction',
                    'label' => '',
                    'attributes' => new ComponentAttributeBag([
                        'route' => route('collaborate.jurisdictions.json.index'),
                        'location' => Location::cached($jurisdiction),
                    ]),
                ]);
            },
            'value' => fn ($value) => Location::cached($value)?->title ?? '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getJurisdictionTypesFilter(bool $forUpdates = false): array
    {
        // selector is limited to location types that apply to the current stream/org
        return [
            'label' => __('geonames.location_type.jurisdiction_types'),
            'render' => function () use ($forUpdates) {
                $component = new Selector('jurisdictionTypes[]', $this->getFilterValue('jurisdictionTypes'), multiple: true, updates: $forUpdates);
                $component->withAttributes(['data-with-remove' => 'data-with-remove']);

                /** @var View */
                $view = $component->render();

                return $view->with($component->data());
            },
            'value' => function ($value) {
                /** @var LocationType|null */
                $jurisdictionType = LocationType::find($value);

                return $jurisdictionType ? __('corpus.location_type.' . $jurisdictionType->adjective_key) : '';
            },
            'multiple' => true,
        ];
    }
}
