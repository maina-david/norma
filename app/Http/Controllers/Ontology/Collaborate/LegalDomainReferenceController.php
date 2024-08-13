<?php

namespace App\Http\Controllers\Ontology\Collaborate;

use App\Models\Corpus\Reference;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;

class LegalDomainReferenceController extends LegalDomainController
{
    /** @var bool */
    protected bool $withShow = false;

    protected Reference $reference;

    /**
     * Generate a new query builder instance for the resource.
     *
     * @param Request $request
     *
     * @return Builder
     */
    protected function baseQuery(Request $request): Builder
    {
        /** @var Route $route */
        $route = $request->route();

        $referenceId = $route->parameter('reference');
        /** @var Reference */
        $reference = Reference::findOrFail($referenceId);
        $this->reference = $reference;

        return $reference->legalDomains()
            ->getQuery();
    }

    /**
     * {@inheritDoc}
     */
    protected function authoriseAction(string $action, mixed $arguments = []): void
    {
        // the only action is index at the moment...so just set to viewAny
        $action = 'viewAny';
        $this->authorize('collaborate.corpus.reference.legal-domain.' . $action);
    }

    /**
     * {@inheritDoc}
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.corpus.references.legal-domains';
    }

    /**
     * {@inheritDoc}
     */
    protected static function forResource(): string
    {
        return Reference::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function resourceRouteParams(): array
    {
        /** @var Request $request */
        $request = request();

        return [
            'reference' => $request->route('reference'),
        ];
    }

    protected function viewPath(): ?string
    {
        return 'pages.ontology.collaborate.legal-domain.for-reference';
    }

    /**
     * {@inheritDoc}
     */
    protected function indexViewData(Request $request): array
    {
        return [
            'headings' => false,
            'reference' => $this->reference,
            'fluid' => true,
        ];
    }
}
