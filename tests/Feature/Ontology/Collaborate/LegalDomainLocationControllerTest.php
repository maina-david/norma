<?php

namespace Tests\Feature\Ontology\Collaborate;

use App\Models\Geonames\Location;
use App\Models\Ontology\LegalDomain;
use Tests\Feature\Traits\HasCollaboratePivotCrudTests;
use Tests\TestCase;

class LegalDomainLocationControllerTest extends TestCase
{
    use HasCollaboratePivotCrudTests;

    protected static function resourceRoute(): string
    {
        return 'collaborate.legal-domains.jurisdictions';
    }

    protected static function resource(): string
    {
        return Location::class;
    }

    protected static function forResource(): ?string
    {
        return LegalDomain::class;
    }

    protected static function pivotRelationName(): string
    {
        return 'locations';
    }
}
