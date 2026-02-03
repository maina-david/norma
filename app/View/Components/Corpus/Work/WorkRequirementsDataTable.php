<?php

namespace App\View\Components\Corpus\Work;

use App\Cache\Ontology\Collaborate\CategorySelectorCache;
use App\Models\Corpus\Work;
use App\Models\Ontology\LegalDomain;
use App\Models\Ontology\Tag;
use App\Traits\Bookmarks\UsesBookmarksTableFilter;
use App\Traits\Geonames\UsesJurisdictionFilter;
use App\Traits\UsesControlTopicsFilter;
use App\View\Components\Ui\DataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\View\ComponentAttributeBag;

class WorkRequirementsDataTable extends DataTable
{
    use UsesBookmarksTableFilter;
    use UsesJurisdictionFilter;
    use UsesControlTopicsFilter;

    /**
     * @param Request               $request
     * @param Builder|Relation|null $query
     *
     * @return Builder|Relation
     */
    public function query(Request $request, Builder|Relation|null $query = null): Builder|Relation
    {
        /** @var Builder|Relation */
        return $query;
    }

    /**
     * @return array<string, mixed>
     */
    public function fields(): array
    {
        /** @var Request */
        $request = request();

        return [
            // 'panel_my_report' => [
            //     'render' => fn ($row) => view('partials.corpus.work.my.render-report-work-panel', [
            //         'work' => $row,
            //     ]),
            //     'heading' => '',
            //     'classes' => 'px-1.5',
            // ],
            'panel_my_register' => [
                'render' => fn ($row) => view('partials.corpus.work.my.render-register-work-item', [
                    'work' => $row,
                ]),
                'heading' => '',
                'classes' => 'px-1.5',
            ],
            // 'panel_my' => [
            //     'render' => fn ($row) => view('partials.corpus.work.my.render-work-panel', [
            //         'work' => $row,
            //         'filters' => $request->only(['tags', 'domain', 'jurisdictionType']),
            //     ]),
            //     'heading' => '',
            //     //'classes' => 'px-1.5',
            // ],
        ];
    }

    /**
     * @codeCoverageIgnore
     *
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        return [
            'domains' => [
                'label' => __('ontology.legal_domain.categories'),
                'render' => fn () => view('partials.ontology.my.render-norma-legal-domain-selector', [
                    'value' => $this->getFilterValue('domains'),
                    'name' => 'domains',
                    'multiple' => true,
                    'attributes' => new ComponentAttributeBag(['data-with-remove' => 'data-with-remove']),
                ]),
                'value' => function ($value) {
                    /** @var LegalDomain|null */
                    $domain = LegalDomain::find($value);

                    return $domain?->title ?? __('ontology.legal_domain.all_categories');
                },
                'multiple' => true,
            ],
            // selector is limited to location types that apply to the current stream/org
            'jurisdictionTypes' => $this->getJurisdictionTypesFilter(),
            'tags' => [
                'label' => __('ontology.tag.topics'),
                'render' => function () {
                    $values = $this->getFilterValue('tags');
                    $options = empty($values) ? [] : Tag::whereKey($values)->pluck('title', 'id')->toArray();

                    return view('partials.ontology.my.render-tag-selector', [
                        'value' => $values,
                        'name' => 'tags',
                        'multiple' => true,
                        'route' => route('my.tags.index.json'),
                        'options' => $options,
                        'attributes' => new ComponentAttributeBag(['data-with-remove' => 'data-with-remove']),
                    ]);
                },
                'value' => function ($value) {
                    /** @var Tag|null */
                    $tag = Tag::find($value);

                    return $tag?->title ?? '';
                },
                'multiple' => true,
            ],
            'topics' => [
                'label' => __('ontology.tag.topics'),
                'render' => function () {
                    /** @var array<int, string> $selected */
                    $selected = $this->getFilterValue('topics');
                    $options = [];

                    if (!empty($selected)) {
                        $cached = app(CategorySelectorCache::class)->get();
                        $options = collect($selected)->mapWithKeys(fn ($item) => [$item => $cached[$item] ?? ''])->all();
                    }

                    return view('components.ui.remote-select', [
                        'multiple' => true,
                        'options' => $options,
                        'value' => $selected,
                        'route' => route('my.categories.tagging.search'),
                        'name' => 'topics',
                        'attributes' => new ComponentAttributeBag([
                            'placeholder' => '',
                            'label' => '',
                            'data-with-remove' => 'data-with-remove',
                            'data-load-search-field' => 'no-filter',
                            '@change' => '$dispatch(\'changed\', Array.from($el.selectedOptions).map(function (it) { return it.value }));',
                        ]),
                    ]);
                },
                'value' => function ($value) {
                    /** @var Tag|null */
                    $tag = Tag::find($value);

                    return $tag?->title ?? '';
                },
                'multiple' => true,
            ],
            ...$this->getControlTopicsFilter(false),
            'works' => [
                'label' => null,
                'render' => null,
                'value' => function ($value) {
                    /** @var Work|null */
                    $work = Work::find($value);

                    return $work?->title ?? '';
                },
                'multiple' => true,
            ],
            ...$this->bookmarksFilter(),
        ];
    }
}
