<?php

namespace Tests\Unit\Stores\Assess;

use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Customer\Norma;
use App\Stores\Assess\AssessmentItemResponseStore;
use Tests\TestCase;

class AssessmentItemResponseStoreTest extends TestCase
{
    public function testCreateResponsesForItems(): void
    {
        $norma = Norma::factory()->create();
        $ai = AssessmentItem::factory()->create();
        $items = (new AssessmentItem())->newCollection()->add($ai);

        $count = AssessmentItemResponse::count();
        app(AssessmentItemResponseStore::class)->createResponsesForItems($items, $norma);
        $this->assertGreaterThan($count, AssessmentItemResponse::count());
        $aiResponse = AssessmentItemResponse::first();
        $this->assertNotNull($aiResponse->next_due_at);
    }

    public function testRemoveResponses(): void
    {
        $norma = Norma::factory()->create();
        $aiResponses = AssessmentItemResponse::factory(3)->for($norma)->create();

        $count = AssessmentItemResponse::count();
        app(AssessmentItemResponseStore::class)->removeResponses($aiResponses, $norma);
        $this->assertLessThan($count, AssessmentItemResponse::count());
    }
}
