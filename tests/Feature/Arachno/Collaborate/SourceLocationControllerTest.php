<?php

namespace Tests\Feature\Arachno\Collaborate;

use App\Models\Arachno\Source;
use App\Models\Geonames\Location;
use Tests\Feature\Traits\HasCollaboratePivotCrudTests;
use Tests\TestCase;

class SourceLocationControllerTest extends TestCase
{
    use HasCollaboratePivotCrudTests;

    protected static function resourceRoute(): string
    {
        return 'collaborate.sources.jurisdictions';
    }

    protected static function resource(): string
    {
        return Location::class;
    }

    protected static function forResource(): ?string
    {
        return Source::class;
    }

    protected static function pivotRelationName(): string
    {
        return 'locations';
    }
}
