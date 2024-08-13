<?php

namespace App\Http\Controllers\Corpus\Collaborate;

use App\Contracts\Http\ResourceAction;
use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\Requests\Corpus\CatalogueDocRequest;
use App\Http\ResourceActions\Corpus\FetchCatalogueDocs;
use App\Models\Corpus\CatalogueDoc;
use App\Traits\Arachno\UsesSourceCategoryFilter;
use App\Traits\Arachno\UsesSourceFilter;
use App\Traits\Geonames\UsesJurisdictionFilter;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\View\ComponentAttributeBag;
use Symfony\Component\HttpFoundation\Response;

class CatalogueDocController extends CollaborateController
{
    use UsesSourceFilter;
    use UsesSourceCategoryFilter;
    use UsesJurisdictionFilter;

    /** @var string */
    protected string $sortBy = 'id';

    /** @var string */
    protected string $sortDirection = 'desc';

    /** @var bool */
    protected bool $withDelete = false;

    /** @var bool */
    protected bool $withUpdate = false;

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return CatalogueDoc::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.corpus.catalogue-docs';
    }

    /**
     * @codeCoverageIgnore
     * Get form request to be used when validating the input.
     *
     * @return string
     */
    protected static function resourceFormRequest(): string
    {
        return CatalogueDocRequest::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected static function indexColumns(): array
    {
        return [
            'title' => fn ($row) => static::renderPartial($row, 'title-column'),
            'docs' => fn ($row) => static::renderPartial($row, 'docs-column'),
            'work' => fn ($row) => static::renderPartial($row, 'work-column'),
            'source' => fn ($row) => $row->source?->title . ($row->sourceCategories->isEmpty() ? '' : ' / ' . $row->sourceCategories->pluck('title')->implode(', ')),
            'link' => fn ($row) => static::renderPartial($row, 'link-column'),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function indexViewData(Request $request): array
    {
        return [
            'headings' => false,
            'fluid' => true,
            'paginate' => 50,
        ];
    }

    /**
     * Generate a new query builder instance for the resource.
     *
     * @param Request $request
     *
     * @return Builder
     */
    protected function baseQuery(Request $request): Builder
    {
        /** @var Builder */
        return parent::baseQuery($request)
            ->with(['source', 'work', 'crawler', 'sourceCategories'])
            ->withCount(['docs', 'sourceCategories'])
            ->orderBy('id', 'desc');
    }

    /**
     * Get the resource filters.
     *
     * @return array<string, array<string, mixed>>
     */
    protected function resourceFilters(): array
    {
        return [
            'sources' => $this->getSourceFilter(true),
            'jurisdiction' => $this->getJurisdictionFilter(),
            'source_categories' => $this->getSourceCategoryFilter(),
            'fetched' => [
                'label' => __('corpus.catalogue_doc.fetched'),
                'render' => fn () => view('partials.ui.collaborate.input-filter', [
                    'attributes' => new ComponentAttributeBag([
                        'label' => __('corpus.catalogue_doc.fetched'),
                        'value' => $this->getFilterValue('fetched'),
                        'name' => 'fetched',
                        'type' => 'checkbox',
                    ]),
                ]),
                'value' => fn ($value) => $value === 'true' ? __('interface.yes') : '',
            ],
            'linked' => [
                'label' => __('corpus.catalogue_doc.linked'),
                'render' => fn () => view('partials.ui.collaborate.input-filter', [
                    'attributes' => new ComponentAttributeBag([
                        'label' => __('corpus.catalogue_doc.linked'),
                        'value' => $this->getFilterValue('linked'),
                        'name' => 'linked',
                        'type' => 'checkbox',
                    ]),
                ]),
                'value' => fn ($value) => $value === 'true' ? __('interface.yes') : '',
            ],
            'failed-fetch' => [
                'label' => __('corpus.catalogue_doc.failed_fetch'),
                'render' => fn () => view('partials.ui.collaborate.input-filter', [
                    'attributes' => new ComponentAttributeBag([
                        'label' => __('corpus.catalogue_doc.failed_fetch'),
                        'value' => $this->getFilterValue('failed-fetch'),
                        'name' => 'failed-fetch',
                        'type' => 'checkbox',
                    ]),
                ]),
                'value' => fn ($value) => $value === 'true' ? __('interface.yes') : '',
            ],
        ];
    }

    /**
     * Get the resource actions.
     *
     * @return array<int, ResourceAction>
     */
    protected static function resourceActions(): array
    {
        return [
            new FetchCatalogueDocs(),
        ];
    }

    /**
     * @codeCoverageIgnore
     * {@inheritDoc}
     */
    protected static function baseSearchQuery(Request $request): \Laravel\Scout\Builder
    {
        $sources = $request->input('sources');

        return CatalogueDoc::search($request->get('search'))
            ->when($sources, fn ($query) => $query->where('source_id', $sources));
    }

    /**
     * Pre-Store hook.
     *
     * @param CatalogueDoc $model
     * @param Request      $request
     *
     * @return void
     */
    protected function preStore(Model $model, Request $request): void
    {
        $model->setUid();
    }

    /**
     * Which route to redirect to after store action.
     *
     * @param CatalogueDoc $model
     *
     * @return string
     */
    protected function redirectAfterStore(Model $model): string
    {
        return route('collaborate.corpus.catalogue-docs.docs.index', ['catalogueDoc' => $model->id]);
    }

    /**
     * {@inheritDoc}
     */
    public function show(Request $request, int $resourceId): View|Response
    {
        return redirect()->route('collaborate.corpus.catalogue-docs.docs.index', ['catalogueDoc' => $resourceId]);
    }
}
