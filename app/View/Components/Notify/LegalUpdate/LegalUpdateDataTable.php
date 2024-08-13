<?php

namespace App\View\Components\Notify\LegalUpdate;

use App\Enums\Notify\LegalUpdateStatus;
use App\Models\Notify\LegalUpdate;
use App\Models\Ontology\LegalDomain;
use App\Traits\Bookmarks\UsesBookmarksTableFilter;
use App\Traits\Geonames\UsesJurisdictionFilter;
use App\Traits\UsesDateRangeFilters;
use App\View\Components\Ui\DataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\View\ComponentAttributeBag;

class LegalUpdateDataTable extends DataTable
{
    use UsesDateRangeFilters;
    use UsesBookmarksTableFilter;
    use UsesJurisdictionFilter;

    /**
     * @param Request                    $request
     * @param Builder|Relation|null|null $query
     *
     * @return Builder|Relation
     */
    public function query(Request $request, Builder|Relation|null $query = null): Builder|Relation
    {
        $query = $query ?? (new LegalUpdate())->newQuery();

        // @phpstan-ignore-next-line
        return $query->filter($request->all());
    }

    /**
     * @return array<string, mixed>
     */
    public function fields(): array
    {
        return [
            'detailed' => [
                'render' => fn ($row) => view('partials.notify.legal-update.detailed-list-item', [
                    'update' => $row,
                ]),
                'heading' => __('interface.title'),
            ],
            'simple' => [
                'render' => fn ($row) => view('partials.notify.legal-update.simple-list-item', [
                    'update' => $row,
                ]),
                'heading' => __('interface.title'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        return [
            'jurisdictionTypes' => $this->getJurisdictionTypesFilter(true),
            'status' => [
                'label' => __('notify.legal_update.status.read_status'),
                'render' => fn () => view('components.notify.my.legal-update-status-selector', [
                    'value' => $this->getFilterValue('status'),
                    'attributes' => new ComponentAttributeBag(['@change' => '$dispatch(\'changed\', $el.value)']),
                    'name' => 'status',
                ]),
                'value' => fn ($value) => LegalUpdateStatus::lang()[LegalUpdateStatus::fromValue((int) $value)->value],
            ],
            'domains' => [
                'label' => __('ontology.legal_domain.categories'),
                'render' => fn () => view('partials.ontology.my.render-libryo-legal-domain-selector', [
                    'value' => $this->getFilterValue('domains'),
                    'name' => 'domain',
                ]),
                'multiple' => true,
                'value' => fn ($value) => LegalDomain::find($value)?->title ?? '',
            ],
            'from' => $this->getFromFilter(),
            'to' => $this->getToFilter(),
            ...$this->bookmarksFilter(),
        ];
    }
}
