<?php

namespace Tests\Unit\Actions\Corpus\Reference;

use App\Actions\Corpus\Reference\BulkAttachTaggables;
use App\Enums\Corpus\MetaChangeStatus;
use App\Enums\Corpus\ReferenceType;
use App\Models\Assess\AssessmentItem;
use App\Models\Assess\Pivots\AssessmentItemReference;
use App\Models\Assess\Pivots\AssessmentItemReferenceDraft;
use App\Models\Auth\User;
use App\Models\Compilation\ContextQuestion;
use App\Models\Compilation\Pivots\ContextQuestionReference;
use App\Models\Compilation\Pivots\ContextQuestionReferenceDraft;
use App\Models\Corpus\Pivots\LegalDomainReference;
use App\Models\Corpus\Pivots\LegalDomainReferenceDraft;
use App\Models\Corpus\Pivots\LocationReference;
use App\Models\Corpus\Pivots\LocationReferenceDraft;
use App\Models\Corpus\Pivots\ReferenceTag;
use App\Models\Corpus\Pivots\ReferenceTagDraft;
use App\Models\Corpus\Reference;
use App\Models\Geonames\Location;
use App\Models\Ontology\Category;
use App\Models\Ontology\LegalDomain;
use App\Models\Ontology\Pivots\CategoryReference;
use App\Models\Ontology\Pivots\CategoryReferenceDraft;
use App\Models\Ontology\Tag;
use Tests\TestCase;

class BulkAttachTaggablesTest extends TestCase
{
    public function testHandle(): void
    {
        /** @var Reference */
        $workRef = Reference::factory()->create(['referenceable_type' => 'works', 'type' => ReferenceType::work()->value]);
        /** @var Reference */
        $citationRef = Reference::factory()->create(['referenceable_type' => 'citations', 'parent_id' => $workRef->id]);
        /** @var Reference */
        $citationRef2 = Reference::factory()->create(['referenceable_type' => 'citations', 'parent_id' => $workRef->id]);

        /** @var LegalDomain */
        $legalDomainExisting = LegalDomain::factory()->create();
        /** @var LegalDomain */
        $legalDomain = LegalDomain::factory()->create();
        $citationRef2->legalDomainDrafts()->attach($legalDomainExisting);

        /** @var User */
        $user = User::factory()->create();
        app(BulkAttachTaggables::class)->handle(
            [
                $citationRef->id,
                $citationRef2->id,
            ],
            [$legalDomain->id],
            LegalDomainReference::class,
            LegalDomainReferenceDraft::class,
            $user,
        );

        $citationRef->refresh();
        $citationRef2->refresh();
        $this->assertTrue($citationRef->legalDomainDrafts->contains($legalDomain));
        $this->assertTrue($citationRef2->legalDomainDrafts->contains($legalDomain));
        $this->assertTrue($citationRef2->legalDomainDrafts->contains($legalDomainExisting));
        $this->assertSame(
            MetaChangeStatus::ADD->value,
            $citationRef->legalDomainDrafts()
                ->withPivot('change_status')
                ->first()
                ->pivot
                ->change_status
        );

        // apply only to work ref and it should still attach to descendants
        /** @var Tag */
        $tag = Tag::factory()->create();
        app(BulkAttachTaggables::class)->handle(
            [$workRef->id],
            [$tag->id],
            ReferenceTag::class,
            ReferenceTagDraft::class
        );

        $citationRef->refresh();
        $this->assertTrue($citationRef->legalDomainDrafts->contains($legalDomain));
        $this->assertTrue($citationRef->tagDrafts->contains($tag));

        /** @var AssessmentItem */
        $taggable = AssessmentItem::factory()->create();
        app(BulkAttachTaggables::class)->handle(
            [$workRef->id],
            [$taggable->id],
            AssessmentItemReference::class,
            AssessmentItemReferenceDraft::class
        );
        $citationRef->refresh();
        // AI's don't inherit
        $this->assertFalse($citationRef->assessmentItemDrafts->contains($taggable));

        /** @var ContextQuestion */
        $taggable = ContextQuestion::factory()->create();
        app(BulkAttachTaggables::class)->handle(
            [$workRef->id],
            [$taggable->id],
            ContextQuestionReference::class,
            ContextQuestionReferenceDraft::class
        );
        $citationRef->refresh();
        // Context questions don't inherit
        $this->assertFalse($citationRef->contextQuestionDrafts->contains($taggable));

        /** @var Location */
        $taggable = Location::factory()->create();
        app(BulkAttachTaggables::class)->handle(
            [$workRef->id],
            [$taggable->id],
            LocationReference::class,
            LocationReferenceDraft::class
        );
        $citationRef->refresh();
        $this->assertTrue($citationRef->locationDrafts->contains($taggable));

        /** @var Category */
        $taggable = Category::factory()->create();
        app(BulkAttachTaggables::class)->handle(
            [$workRef->id],
            [$taggable->id],
            CategoryReference::class,
            CategoryReferenceDraft::class
        );
        $citationRef->refresh();
        $this->assertTrue($citationRef->categoryDrafts->contains($taggable));
    }
}
