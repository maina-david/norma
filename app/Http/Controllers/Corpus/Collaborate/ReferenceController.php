<?php

namespace App\Http\Controllers\Corpus\Collaborate;

use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use App\Traits\Geonames\UsesJurisdictionFilter;
use App\Traits\Ontology\UsesLegalDomainFilter;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use function HotwiredLaravel\TurboLaravel\dom_id;

class ReferenceController extends CollaborateController
{
    use UsesJurisdictionFilter;
    use UsesLegalDomainFilter;

    /** @var string */
    protected string $sortBy = 'id';

    /** @var bool */
    protected bool $withCreate = false;

    /** @var bool */
    protected bool $withDelete = false;

    /**
     * Generate a new query builder instance for the resource.
     *
     * @param Request $request
     *
     * @return Builder
     */
    protected function baseQuery(Request $request): Builder
    {
        return parent::baseQuery($request);
    }

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @phpstan-return class-string
     *
     * @return string
     */
    protected static function resource(): string
    {
        return Reference::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.corpus.references';
    }

    /**
     * Get form request to be used when validating the input.
     *
     * @codeCoverageIgnore
     *
     * @return string
     */
    protected static function resourceFormRequest(): string
    {
        return '';
    }

    /**
     * TODO: remove codeCoverageIgnore if implemented.
     *
     * @codeCoverageIgnore
     * {@inheritDoc}
     */
    protected static function indexColumns(): array
    {
        return [
            // 'title' => fn ($row) => self::renderPartial($row, 'title-column'),
        ];
    }

    // /**
    //  * {@inheritDoc}
    //  */
    // protected function resourceFilters(): array
    // {
    //     return [
    //         'jurisdiction' => $this->getJurisdictionFilter(),
    //         'domains' => $this->getLegalDomainFilter(),
    //     ];
    // }

    /**
     * {@inheritDoc}
     */
    protected function showViewData(Request $request): array
    {
        return [
            'formWidth' => 'max-w-7xl',
            'fluid' => true,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function show(Request $request, int $resourceId): View|Response
    {
        if (!$request->isForTurboFrame()) {
            /** @var View */
            return parent::show($request, $resourceId);
        }

        /** @var Work $resource */
        $resource = $this->baseQuery($request)
            ->with(['htmlContent:reference_id,cached_content'])
            ->findOrFail($resourceId);

        if (method_exists($this, 'authoriseAction')) {
            $this->authoriseAction('show', $resource);
        }

        return singleTurboStreamResponse(dom_id($resource), 'update')
            ->view('pages.corpus.collaborate.reference.partials.reference-details', [
                'resource' => $resource,
                'reference' => $resource,
            ])
            ->toResponse($request);
    }
}
