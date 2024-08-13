<?php

namespace Tests\Unit\Services\Workflows;

use App\Models\Corpus\Work;
use App\Models\Corpus\WorkExpression;
use App\Models\Geonames\Location;
use App\Models\Ontology\LegalDomain;
use App\Models\Workflows\Project;
use App\Services\Workflows\ForProjectDocumentCreator;
use Tests\TestCase;

class ForProjectDocumentCreatorTest extends TestCase
{
    public function testCreateDocument(): void
    {
        /** @var Work */
        $work = Work::factory()->create();
        /** @var WorkExpression */
        $workExpression = WorkExpression::factory()->create(['work_id' => $work->id]);
        /** @var LegalDomain */
        $legalDomain = LegalDomain::factory()->create();
        /** @var Location */
        $location = Location::factory()->create();

        /** @var Project */
        $project = Project::factory()->create(['location_id' => $location->id]);
        $project->legalDomains()->attach($legalDomain);

        $document = app(ForProjectDocumentCreator::class)->createCollaborateDocument($workExpression, $project, $project->location_id);

        $this->assertTrue($document->legalDomains()->get()->contains($legalDomain));
        $this->assertTrue($document->locations()->get()->contains($location));
        $this->assertSame($workExpression->id, $document->work_expression_id);
    }
}
