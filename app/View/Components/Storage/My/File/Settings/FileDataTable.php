<?php

namespace App\View\Components\Storage\My\File\Settings;

use App\Models\Geonames\Location;
use App\Models\Ontology\LegalDomain;
use App\Models\Storage\My\File;
use App\View\Components\Ui\DataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class FileDataTable extends DataTable
{
    /**
     * @param Request                    $request
     * @param Builder|Relation|null|null $query
     *
     * @return Builder|Relation
     */
    public function query(Request $request, Builder|Relation|null $query = null): Builder|Relation
    {
        $query = $query ?? (new File())->newQuery();

        // @phpstan-ignore-next-line
        return $query->with(['locations', 'legalDomains'])->filter($request->all());
    }

    /**
     * @return array<string, mixed>
     */
    public function fields(): array
    {
        return [
            'title' => [
                'render' => fn ($row) => view('components.storage.my.file.settings.global.file-column', [
                    'file' => $row,
                ]),
                'heading' => __('interface.name'),
            ],
        ];
    }

    /**
     * @return array<string,mixed>
     */
    public function filters(): array
    {
        return [
            'jurisdictions' => [
                'label' => __('geonames.location.jurisdiction'),
                'render' => fn () => view('partials.geonames.location.render-selector', [
                    'label' => '',
                ]),
                'multiple' => true,
                // @phpstan-ignore-next-line
                'value' => fn ($value) => Location::find($value)?->title,
            ],
            'domains' => [
                'label' => __('assess.category'),
                'render' => fn () => view('components.ontology.legal-domain.selector', [
                    'label' => '',
                ]),
                // @phpstan-ignore-next-line
                'value' => fn ($value) => $value ? LegalDomain::find($value)->title : '',
                'multiple' => true,
            ],
        ];
    }
}
