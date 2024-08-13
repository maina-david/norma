<?php

namespace Tests\Unit\Stores\Assess;

use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Customer\Libryo;
use App\Stores\Assess\AssessmentItemResponseStore;
use Tests\TestCase;

class AssessmentItemResponseStoreTest extends TestCase
{
    public function testCreateResponsesForItems(): void
    {
        $libryo = Libryo::factory()->create();
        $ai = AssessmentItem::factory()->create();
        $items = (new AssessmentItem())->newCollection()->add($ai);

        $count = AssessmentItemResponse::count();
        app(AssessmentItemResponseStore::class)->createResponsesForItems($items, $libryo);
        $this->assertGreaterThan($count, AssessmentItemResponse::count());
        $aiResponse = AssessmentItemResponse::first();
        $this->assertNotNull($aiResponse->next_due_at);
    }

    public function testRemoveResponses(): void
    {
        $libryo = Libryo::factory()->create();
        $aiResponses = AssessmentItemResponse::factory(3)->for($libryo)->create();

        $count = AssessmentItemResponse::count();
        app(AssessmentItemResponseStore::class)->removeResponses($aiResponses, $libryo);
        $this->assertLessThan($count, AssessmentItemResponse::count());
    }
}
