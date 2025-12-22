<?php

namespace Tests\Feature\Requirements\My;

use App\Enums\Corpus\ReferenceLinkType;
use App\Enums\Corpus\ReferenceType;
use App\Models\Corpus\Pivots\ReferenceReference;
use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceContent;
use App\Models\Requirements\Consequence;
use Tests\Feature\My\MyTestCase;

class ReferenceLinksStreamControllerTest extends MyTestCase
{
    public function testForReference(): void
    {
        [$user, $norma] = $this->initUserNormaOrg();
        $reference = Reference::factory()->create();
        $reference->locations()->attach($norma->primary_location_id);
        $html = $this->faker->randomHtml(1, 2);
        $consequnceHtml = $this->faker->randomHtml(1, 2);
        $parentReference = Reference::factory()->create();
        $parentReference->htmlContent()->save(new ReferenceContent([
            'reference_id' => $parentReference->id,
            'cached_content' => $html,
        ]));
        $consequenceGroupReference = Reference::factory()->create(['type' => ReferenceType::consequenceGroup()->value, 'parent_id' => $parentReference->id]);
        $consequenceGroupReference->locations()->attach($norma->primary_location_id);
        $consequenceGroupReference->htmlContent()->save(new ReferenceContent([
            'reference_id' => $consequenceGroupReference->id,
            'cached_content' => $consequnceHtml,
        ]));
        $consequenceGroupReference->load('refPlainText');
        $title = $consequenceGroupReference->refPlainText->plain_text;

        $consequence = Consequence::factory()->create();

        $consequenceGroupReference->consequences()->attach($consequence);

        $reference->raisesConsequenceGroups()->attach($consequenceGroupReference, [
            'parent_work_id' => $reference->work_id,
            'child_work_id' => $consequenceGroupReference->work_id,
            'link_type' => 3,
        ]);

        $this->get(route('my.consequences.for.reference', ['reference' => $reference]))
            ->assertSuccessful()
            ->assertDontSeeText(strip_tags($html))
            ->assertSeeText($title)
            ->assertDontSeeText($consequence->toReadableString());

        $common = [
            'parent_work_id' => $reference->work_id,
            'child_work_id' => $consequenceGroupReference->work_id,
            'parent_id' => $reference->id,
            'child_id' => $consequenceGroupReference->id,
        ];

        ReferenceReference::insert([...$common, 'link_type' => ReferenceLinkType::READ_WITH->value]);
        ReferenceReference::insert([...$common, 'link_type' => ReferenceLinkType::AMENDMENT->value]);

        $this->get(route('my.amendments.for.reference', ['reference' => $reference]))
            ->assertSuccessful()
            ->assertDontSeeText(strip_tags($html))
            ->assertSeeText($title)
            ->assertDontSeeText($consequence->toReadableString());

        $this->get(route('my.read-withs.for.reference', ['reference' => $reference]))
            ->assertSuccessful()
            ->assertDontSeeText(strip_tags($html))
            ->assertSeeText($title)
            ->assertDontSeeText($consequence->toReadableString());
    }
}
