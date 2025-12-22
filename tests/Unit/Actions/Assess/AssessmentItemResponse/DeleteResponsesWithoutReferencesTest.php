<?php

namespace Tests\Unit\Actions\Assess\AssessmentItemResponse;

use App\Actions\Assess\AssessmentItemResponse\DeleteResponsesWithoutReferences;
use App\Enums\Assess\ResponseStatus;
use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Corpus\Reference;
use App\Models\Customer\Norma;
use Tests\TestCase;

class DeleteResponsesWithoutReferencesTest extends TestCase
{
    public function testHandle(): void
    {
        $norma = Norma::factory()->create();
        $ref = Reference::factory()->create();
        $ref->normas()->attach($norma->id);

        $ai = AssessmentItem::factory()->create();
        $aiResponse = AssessmentItemResponse::factory()->for($norma)->for($ai)->create(['answer' => ResponseStatus::notAssessed()->value]);
        $aiResponseYes = AssessmentItemResponse::factory()->for($norma)->for($ai)->create(['answer' => ResponseStatus::yes()->value]);
        $ai2 = AssessmentItem::factory()->create();
        $ref->assessmentItems()->attach($ai2);
        $aiResponseWithReference = AssessmentItemResponse::factory()->for($norma)->for($ai2)->create(['answer' => ResponseStatus::notAssessed()->value]);

        $this->assertNotNull(AssessmentItemResponse::find($aiResponse->id));
        app(DeleteResponsesWithoutReferences::class)->handle();

        $this->assertNull(AssessmentItemResponse::find($aiResponse->id));
        $this->assertNotNull(AssessmentItemResponse::find($aiResponseWithReference->id));
        $this->assertNotNull(AssessmentItemResponse::find($aiResponseYes->id));
    }
}
