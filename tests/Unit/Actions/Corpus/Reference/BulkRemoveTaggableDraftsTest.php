<?php

namespace Tests\Unit\Actions\Corpus\Reference;

use App\Actions\Corpus\Reference\BulkRemoveTaggableDrafts;
use App\Enums\Corpus\ReferenceType;
use App\Models\Corpus\Pivots\LegalDomainReferenceDraft;
use App\Models\Corpus\Reference;
use App\Models\Ontology\LegalDomain;
use Tests\TestCase;

class BulkRemoveTaggableDraftsTest extends TestCase
{
    public function testHandle(): void
    {
        /** @var Reference */
        $workRef = Reference::factory()->create(['referenceable_type' => 'works', 'type' => ReferenceType::work()->value]);
        /** @var Reference */
        $citationRef = Reference::factory()->create(['referenceable_type' => 'citations', 'parent_id' => $workRef->id]);
        /** @var LegalDomain */
        $legalDomain = LegalDomain::factory()->create();
        $citationRef->legalDomainDrafts()->attach($legalDomain);

        app(BulkRemoveTaggableDrafts::class)->handle(
            [$workRef->id],
            [$legalDomain->id],
            LegalDomainReferenceDraft::class
        );

        $citationRef->refresh();
        $this->assertTrue($citationRef->legalDomainDrafts->isEmpty());
    }
}
