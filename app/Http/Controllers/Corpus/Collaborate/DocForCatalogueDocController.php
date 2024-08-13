<?php

namespace App\Http\Controllers\Corpus\Collaborate;

use App\Actions\Corpus\Doc\LinkToWork;
use App\Actions\Corpus\Work\CreateFromDoc;
use App\Http\Requests\Corpus\DocForCatalogueDocRequest;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\ContentResource;
use App\Models\Corpus\Doc;
use App\Services\Arachno\Frontier\DocHashGenerator;
use App\Stores\Corpus\ContentResourceStore;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class DocForCatalogueDocController extends DocController
{
    /** @var bool */
    protected bool $searchable = false;

    /** @var bool */
    protected bool $withCreate = true;

    protected static function forResource(): ?string
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
        return 'collaborate.corpus.catalogue-docs.docs';
    }

    /**
     * @codeCoverageIgnore
     * Get form request to be used when validating the input.
     *
     * @return string
     */
    protected static function resourceFormRequest(): string
    {
        //        return '';
        return DocForCatalogueDocRequest::class;
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
        /** @var CatalogueDoc $catalogueDoc */
        $catalogueDoc = $this->getForResource();

        /** @var Builder */
        return parent::baseQuery($request)
            ->whereRelation('catalogueDoc', fn ($q) => $q->whereKey($catalogueDoc->id));
    }

    /**
     * {@inheritDoc}
     */
    protected function resourceRouteParams(): array
    {
        /** @var Request $request */
        $request = request();

        return [
            'catalogueDoc' => $request->route('catalogueDoc'),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function indexViewData(Request $request): array
    {
        /** @var CatalogueDoc $catalogueDoc */
        $catalogueDoc = $this->getForResource();
        $catalogueDoc->load(['crawler']);

        return [
            'preCreateButtonView' => 'partials.corpus.catalogue-doc.fetch-doc-button',
            'catalogueDoc' => $catalogueDoc,
        ];
    }

    /**
     * Store the resource.
     *
     * @param Request $request
     *
     * @return ContentResource
     */
    protected function updateResource(Request $request): ContentResource
    {
        /** @var UploadedFile $file */
        $file = $request->file('content_resource_file');

        return app(ContentResourceStore::class)->storeResource($file->getContent(), $file->getClientMimeType());
    }

    /**
     * Pre-Store hook.
     *
     * @param Doc                       $model
     * @param DocForCatalogueDocRequest $request
     *
     * @return void
     */
    protected function preStore(Model $model, Request $request): void
    {
        $catalogue = CatalogueDoc::findOrFail($request->route('catalogueDoc'));
        $model->primary_location_id = $catalogue->primary_location_id;
        $model->source_unique_id = $catalogue->source_unique_id;
        $model->source_id = $catalogue->source_id;
        $model->crawler_id = $catalogue->crawler_id;
        $model->first_content_resource_id = $this->updateResource($request)->id;
        $model->setUid();
    }

    /**
     * Post-Store hook.
     *
     * @param Doc                       $model
     * @param DocForCatalogueDocRequest $request
     *
     * @return void
     */
    protected function postStore(Model $model, Request $request): void
    {
        $catalogue = CatalogueDoc::with(['work'])->findOrFail($request->route('catalogueDoc'));
        $latestDoc = $catalogue->docs()->with(['docMeta'])->orderBy('id', 'desc')->first();
        $model->load(['docMeta']);

        app(DocHashGenerator::class)->setHash($model);

        $model->docMeta->update([
            'title_translation' => $request->get('title_translation'),
            'language_code' => $catalogue->language_code,
            'work_id' => $catalogue->work->id ?? null,
            'source_url' => $request->get('source_url'),
            'download_url' => $request->get('download_url'),
            'work_type_id' => $request->get('work_type_id'),
            'work_number' => $request->get('work_number', $latestDoc->docMeta->work_number ?? null),
            'publication_number' => $request->get('publication_number', $latestDoc->docMeta->publication_number ?? null),
            'publication_document_number' => $request->get('publication_document_number', $latestDoc->docMeta->publication_document_number ?? null),
            'work_date' => $request->get('work_date', $latestDoc->docMeta->work_date ?? now()->format('Y-m-d')),
            'effective_date' => $request->get('effective_date', $latestDoc->docMeta->effective_date ?? now()->format('Y-m-d')),
        ]);

        if ($catalogue->work) {
            app(LinkToWork::class)->handle($model, $catalogue->work);
        } else {
            app(CreateFromDoc::class)->handle($model);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function createViewData(Request $request): array
    {
        $catalogue = CatalogueDoc::findOrFail($request->route('catalogueDoc'));
        $doc = $catalogue->docs()->with(['docMeta'])->orderBy('id', 'desc')->first();

        return [
            'enctype' => 'multipart/form-data',
            'resource' => $doc,
        ];
    }
}
