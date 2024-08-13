<?php

namespace App\View\Components\Geonames;

use App\Models\Geonames\CountryInfo;
use Closure;
use Illuminate\View\Component;

class CountryCodeSelector extends Component
{
    /** @var array<string, string> */
    public array $codes = [];

    /** @var string|null */
    public ?string $value;

    /** @var string */
    public string $name;

    /** @var string|null */
    public ?string $label;

    /** @var bool */
    public bool $required;

    /**
     * Create a new component instance.
     *
     * @param string      $name
     * @param string|null $label
     * @param string|null $value
     * @param bool        $noCode
     * @param bool        $required
     * @param string      $valueColumn
     */
    public function __construct(
        string $name,
        ?string $label = null,
        ?string $value = null,
        bool $noCode = false,
        bool $required = false,
        string $valueColumn = 'iso_alpha2'
    ) {
        $this->codes[''] = 'Please select...';
        $this->value = $value ?? old($name);
        $this->name = $name;
        $this->label = $label;
        $this->required = $required;

        CountryInfo::whereNotNull($noCode ? $valueColumn : 'Phone')
            ->get(['name', 'Phone', $valueColumn])
            ->sortBy('name')
            ->each(function ($info) use ($valueColumn, $noCode) {
                if ($noCode) {
                    $this->codes[(string) $info->{$valueColumn}] = $info->name ?? '';

                    return;
                }

                // @todo should probably cover this in tests
                // @codeCoverageIgnoreStart
                /** @var string|null $phone */
                $phone = $info->Phone;
                if ($phone && !str_starts_with($phone, '+')) {
                    $phone = "+{$phone}";
                }

                if ($phone && $phone !== '') {
                    $this->codes[$phone] = "{$info->name} ({$phone})";
                }
                // @codeCoverageIgnoreEnd
            });
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|Closure|string
     */
    public function render()
    {
        return view('components.geonames.country-code-selector');
    }
}
