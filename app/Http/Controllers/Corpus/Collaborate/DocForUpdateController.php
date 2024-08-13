<?php

namespace App\Http\Controllers\Corpus\Collaborate;

use App\Actions\Notify\LegalUpdate\CreateFromDoc;
use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\Controllers\Traits\HasShowAction;
use App\Models\Corpus\Doc;
use App\Traits\Arachno\UsesSourceFilter;
use App\Traits\Geonames\UsesJurisdictionFilter;
use App\Traits\Ontology\UsesLegalDomainFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DocForUpdateController extends CollaborateController
{
    use HasShowAction;
    use UsesJurisdictionFilter;
    use UsesSourceFilter;
    use UsesLegalDomainFilter;

    /** @var string */
    protected string $sortBy = 'id';

    /** @var string */
    protected string $sortDirection = 'desc';

    /** @var bool */
    protected bool $withCreate = false;

    /** @var bool */
    protected bool $withDelete = false;

    /** @var bool */
    protected bool $withUpdate = false;

    /**
     * {@inheritDoc}
     */
    public static function shouldAuthorise(): bool
    {
        return false;
    }

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return Doc::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.corpus.docs.for-update';
    }

    /**
     * @codeCoverageIgnore
     * Get form request to be used when validating the input.
     *
     * @return string
     */
    protected static function resourceFormRequest(): string
    {
        return '';
    }

    /**
     * @return array<string, mixed>
     */
    protected static function indexColumns(): array
    {
        return [
            'title' => fn ($row) => static::renderPartial($row, 'title-column'),
            'preview' => fn ($row) => static::renderPartial($row, 'preview-column'),
            'text_preview' => fn ($row) => static::renderPartial($row, 'text-column'),
            'update' => fn ($row) => static::renderPartial($row, 'update-column'),
            'date_created' => fn ($row) => view('partials.ui.data-table.date-column', ['date' => $row->created_at])->render(),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function showViewData(Request $request): array
    {
        return [
            'baseRoute' => 'collaborate.corpus.docs.for-update',
            'formWidth' => 'max-w-4xl',
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
        return Doc::forUpdate()
            ->with([
                'docMeta',
                'source',
                'legalDomains:id,title',
                'primaryLocation:id,flag,title',
                'legalUpdates:id,created_from_doc_id',
                'firstContentResource.textContentResource:id,path',
                'categories:id,display_label',
                'keywords:id,label',
            ])
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
            'jurisdiction' => $this->getJurisdictionFilter(),
            'source' => $this->getSourceFilter(),
            'domains' => $this->getLegalDomainFilter(),
        ];
    }

    public function createUpdateFromDoc(CreateFromDoc $createFromDoc, Doc $doc): RedirectResponse
    {
        $createFromDoc->handle($doc);
        $this->notifyGeneralSuccess();

        return back();
    }
}
