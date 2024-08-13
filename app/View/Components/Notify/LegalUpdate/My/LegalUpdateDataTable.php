<?php

namespace App\View\Components\Notify\LegalUpdate\My;

use App\Enums\Notify\LegalUpdatePublishedStatus;
use App\Models\Notify\LegalUpdate;
use App\Traits\Geonames\UsesJurisdictionFilter;
use App\View\Components\Ui\DataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\View\ComponentAttributeBag;

class LegalUpdateDataTable extends DataTable
{
    use UsesJurisdictionFilter;

    /**
     * @param Request                    $request
     * @param Builder|Relation|null|null $query
     *
     * @return Builder|Relation
     */
    public function query(Request $request, Builder|Relation|null $query = null): Builder|Relation
    {
        $query = $query ?? (new LegalUpdate())->newQuery()->withCount(['libraries', 'libryos']);

        // @phpstan-ignore-next-line
        return $query->filter($request->all());
    }

    /**
     * @return array<string, mixed>
     */
    public function fields(): array
    {
        return [
            'id' => [
                'render' => fn ($row) => $row['id'],
                'heading' => __('interface.id'),
            ],
            'title' => [
                'render' => fn ($row) => view('partials.notify.legal-update.columns.title', ['row' => $row]),
                'heading' => __('interface.title'),
            ],
            'release_at' => [
                'render' => fn ($row) => view('partials.notify.legal-update.columns.nowrap', [
                    'content' => $row['release_at']?->format('d M Y'),
                ]),
                'heading' => __('notify.legal_update.release_date'),
            ],
            'created_at' => [
                'render' => fn ($row) => view('partials.notify.legal-update.columns.nowrap', [
                    'content' => $row['created_at']?->format('d M Y'),
                ]),
                'heading' => __('notify.legal_update.created_at'),
            ],
            'libraries' => [
                'render' => fn ($row) => view('partials.notify.legal-update.columns.count', [
                    'count' => $row['libraries_count'],
                    'row' => $row,
                ]),
                'heading' => __('notify.legal_update.libraries_notified'),
            ],
            'streams' => [
                'render' => fn ($row) => view('partials.notify.legal-update.columns.count', [
                    'count' => $row['libryos_count'],
                    'row' => $row,
                ]),
                'heading' => __('notify.legal_update.streams_notified'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        return [
            'jurisdiction' => $this->getJurisdictionFilter(),
            'publish-status' => [
                'label' => __('corpus.work.status'),
                'render' => function () {
                    return view('partials.ui.collaborate.input-filter', [
                        'attributes' => new ComponentAttributeBag([
                            'type' => 'select',
                            'name' => 'publish-status',
                            'options' => LegalUpdatePublishedStatus::forSelector(true),
                        ]),
                    ]);
                },
                'value' => fn ($value) => LegalUpdatePublishedStatus::tryFrom($value)?->label() ?? '',
            ],
        ];
    }
}
