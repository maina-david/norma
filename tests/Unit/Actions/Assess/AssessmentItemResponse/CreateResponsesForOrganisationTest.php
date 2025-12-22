<?php

namespace Tests\Unit\Actions\Assess\AssessmentItemResponse;

use App\Actions\Assess\AssessmentItemResponse\CreateResponsesForOrganisation;
use App\Enums\System\NormaModule;
use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Corpus\Reference;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use Tests\TestCase;

class CreateResponsesForOrganisationTest extends TestCase
{
    public function testHandle(): void
    {
        $org = Organisation::factory()->create();
        $normas = Norma::factory(3)->for($org)->create();
        $normas->each(fn ($l) => $l->enableModule(NormaModule::comply()));
        $ref = Reference::factory()->create();
        $ref->normas()->attach($normas->modelKeys());
        $ai = AssessmentItem::factory()->create();

        $count = AssessmentItemResponse::count();
        app(CreateResponsesForOrganisation::class)->handle($org);

        $this->assertSame($count, AssessmentItemResponse::count());

        $ai->references()->attach($ref->id);
        app(CreateResponsesForOrganisation::class)->handle($org);
        $this->assertGreaterThan($count, AssessmentItemResponse::count());
    }
}
