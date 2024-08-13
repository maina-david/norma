<?php

namespace App\Http\Controllers\Traits;

use App\Models\Geonames\CountryInfo;
use App\View\Components\Geonames\CountryCodeSelector;
use Illuminate\Support\Facades\Blade;

trait HasNationalityAndResidence
{
    /**
     * Get the nationality and residence filters.
     *
     * @return array<string, array<string, mixed>>
     */
    protected function nationalityAndResidenceFilter(): array
    {
        return [
            'nationality' => [
                'label' => __('collaborators.nationality'),
                'render' => fn () => $this->countrySelector('nationality'),
                'value' => fn ($value) => $this->getCountryName($value),
            ],
            'residency' => [
                'label' => __('collaborators.current_residency'),
                'render' => fn () => $this->countrySelector('residency'),
                'value' => fn ($value) => $this->getCountryName($value),
            ],
        ];
    }

    /**
     * Get the country name.
     *
     * @param string|null $value
     *
     * @return string
     */
    private function getCountryName(?string $value): string
    {
        if (!$value) {
            // @codeCoverageIgnoreStart
            return '';
            // @codeCoverageIgnoreEnd
        }

        /** @var CountryInfo|null $info */
        $info = CountryInfo::where('iso_alpha2', $value)->first();

        return $info->name ?? '';
    }

    /**
     * Get the country selector.
     *
     * @param string $name
     *
     * @return string
     */
    protected function countrySelector(string $name): string
    {
        /** @var string $value */
        $value = method_exists($this, 'getFilterValue') ? $this->getFilterValue($name) : '';

        $component = new CountryCodeSelector($name, '', $value, true);
        $component->withAttributes([
            '@change' => '$dispatch(\'changed\', $el.value)',
        ]);

        return Blade::renderComponent($component);
    }
}
