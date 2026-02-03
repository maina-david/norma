<?php

namespace App\View\Components\Corpus\Reference;

use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use App\Models\Geonames\LocationType;
use App\Models\Ontology\LegalDomain;
use App\Models\Ontology\Tag;
use App\View\Components\Geonames\LocationType\My\Selector;
use App\View\Components\Ui\DataTable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class ReferenceDataTable extends DataTable
{
    /**
     * @param Request                    $request
     * @param Builder|Relation|null|null $query
     *
     * @return Builder|Relation
     */
    public function query(Request $request, Builder|Relation|null $query = null): Builder|Relation
    {
        // @codeCoverageIgnoreStart
        $query = $query ?? (new Reference())->newQuery();

        // filtering needs to happen in controller, as we use elasticsearch for the search filter

        return $query;
        // @codeCoverageIgnoreEnd
    }

    /**
     * @return array<string, mixed>
     */
    public function fields(): array
    {
        return [
            'title_for_selector' => [
                'render' => fn ($row) => view('partials.corpus.reference.title-for-selector', [
                    'reference' => $row,
                ]),
                'heading' => '',
            ],
            'my_link' => [
                'render' => fn ($row) => view('partials.ui.link', [
                    'href' => route('my.corpus.references.show', ['reference' => $row->id]),
                    'linkText' => $row->refPlainText?->plain_text,
                ]),
                'heading' => '',
            ],
            'detailed' => [
                'render' => fn ($row) => view('partials.corpus.reference.my.detailed-list-item', [
                    'reference' => $row,
                ]),
                'heading' => '',
            ],
            'detailed_with_unlink' => [
                'render' => fn ($row) => view('partials.corpus.reference.my.detailed-list-item', [
                    'reference' => $row,
                    'response' => request()->route('aiResponse'),
                    'unlink' => true,
                ]),
                'heading' => '',
            ],
            'collaborate-versions' => [
                'render' => fn ($row) => view('partials.corpus.reference.collaborate.versions-column', [
                    'reference' => $row,
                ]),
                'heading' => '',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        return [
            // selector is limited to location types that apply to the current stream/org
            'jurisdictionTypes' => [
                'label' => __('geonames.location_type.jurisdiction_types'),
                'render' => function () {
                    $component = new Selector('jurisdictionTypes', $this->getFilterValue('jurisdictionTypes'));

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
            ],
            'domains' => [
                'label' => __('ontology.legal_domain.categories'),
                'render' => fn () => view('partials.ontology.my.render-norma-legal-domain-selector', [
                    'value' => $this->getFilterValue('domain'),
                    'name' => 'domain',
                ]),
                'value' => function ($value) {
                    /** @var LegalDomain|null */
                    $domain = LegalDomain::find($value);

                    return $domain?->title ?? __('ontology.legal_domain.all_categories');
                },
                'multiple' => true,
            ],
            'tags' => [
                'label' => __('ontology.tag.topics'),
                'render' => fn () => view('partials.ontology.my.render-tag-selector', [
                    'value' => $this->getFilterValue('tags'),
                    'name' => 'tags',
                    'multiple' => false,
                    'placeholder' => __('ontology.tag.search_for_topics'),
                    'route' => route('my.tags.index.json'),
                ]),
                'value' => function ($value) {
                    /** @var Tag|null */
                    $tag = Tag::withTrashed()->find($value);
                    if (is_null($tag)) {
                        /** @var Tag|null */
                        $tag = Tag::where('old_tag_id', $value)->first();
                    }

                    return $tag?->title ?? '';
                },
                'multiple' => true,
            ],
            'works' => [
                'label' => '',
                'render' => fn () => '',
                'value' => function ($value) {
                    /** @var Work|null */
                    $work = Work::find($value);

                    return $work?->title ?? '';
                },
                'multiple' => true,
            ],
        ];
    }
}
