<?php

namespace App\View\Components\Corpus\Work;

use App\Enums\Corpus\WorkStatus;
use App\Models\Corpus\Work;
use App\Models\Geonames\Location;
use App\View\Components\Ui\DataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class WorkDataTable extends DataTable
{
    /**
     * @param Request                    $request
     * @param Builder|Relation|null|null $query
     *
     * @return Builder|Relation
     */
    public function query(Request $request, Builder|Relation|null $query = null): Builder|Relation
    {
        $query = $query ?? (new Work())->newQuery();

        // @phpstan-ignore-next-line
        return $query->filter($request->all());
    }

    /**
     * @return array<string, mixed>
     */
    public function fields(): array
    {
        return [
            // 'title' => [
            //     'render' => fn ($row) => view('partials.ui.link', [
            //         'href' => '',
            //         'linkText' => $row['title'],
            //     ]),
            //     'heading' => __('interface.title'),
            // ],
            'title_with_references' => [
                'render' => fn ($row) => view('partials.corpus.work.with-references', [
                    'work' => $row,
                ]),
                'heading' => __('interface.title'),
            ],
            'title_manual_compilation' => [
                'render' => fn ($row) => view('partials.corpus.work.my.for-manual-compilation-render-title', [
                    'work' => $row,
                ]),
                'heading' => __('interface.title'),
            ],
            'title_for_selector' => [
                'render' => fn ($row) => view('partials.corpus.work.for-selector-render-title', [
                    'work' => $row,
                ]),
                'heading' => __('interface.title'),
            ],
            'locations' => [
                'render' => function ($row) {
                    return $row->references->pluck('locations')->collapse()->unique('id')->implode('title', ', ');
                },
                'heading' => __('geonames.location.jurisdictions'),
            ],
            'status' => [
                'render' => function ($row) {
                    return WorkStatus::lang()[$row['status']] ?? '';
                },
                'heading' => __('corpus.work.status'),
            ],
            'work_type' => [
                'render' => function ($row) {
                    return __('corpus.work.types.' . $row['work_type']);
                },
                'heading' => __('corpus.work.type'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        return [
            'countries' => [
                'label' => __('geonames.location.country'),
                'render' => fn () => view('partials.geonames.location.render-selector'),
                'multiple' => true,
                'value' => fn ($value) => Location::find($value)?->title ?? '',
            ],
        ];
    }
}
