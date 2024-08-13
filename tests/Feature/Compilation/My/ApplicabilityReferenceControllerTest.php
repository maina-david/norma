<?php

namespace Tests\Feature\Compilation\My;

use App\Enums\Compilation\ContextQuestionAnswer;
use App\Enums\Corpus\ReferenceType;
use App\Models\Compilation\ContextQuestion;
use App\Models\Corpus\Reference;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Geonames\Location;
use App\Models\Ontology\LegalDomain;
use App\Services\Customer\ActiveLibryosManager;
use Tests\Feature\My\MyTestCase;

class ApplicabilityReferenceControllerTest extends MyTestCase
{
    public function testDisplayingApplicabilityView(): void
    {
        $user = $this->signIn();
        $location = Location::factory()->create();
        $org = Organisation::factory()->create();
        $libryo = Libryo::factory()->for($org)->create(['location_id' => $location->id]);
        $question = ContextQuestion::factory()->create();
        $noQuestion = ContextQuestion::factory()->create();
        $reference = Reference::factory()->create(['type' => ReferenceType::citation()->value]);
        $legalDomain = LegalDomain::factory()->create();
        $nonLibryoLegalDomain = LegalDomain::factory()->create();

        $libryo->legalDomains()->attach($legalDomain->id);
        $user->libryos()->attach($libryo->id);
        $user->organisations()->attach($org, ['is_admin' => true]);
        $question->libryos()->attach($libryo->id, ['answer' => ContextQuestionAnswer::yes()->value]);
        $noQuestion->libryos()->attach($libryo->id, ['answer' => ContextQuestionAnswer::no()->value]);
        $question->references()->attach([$reference->id]);
        $noQuestion->references()->attach([$reference->id]);
        $reference->legalDomains()->attach([$legalDomain->id, $nonLibryoLegalDomain->id]);

        $this->activateAllStreams($user);

        $this->get(route('my.applicability-reference.index', ['reference' => $reference->id]))
            ->assertSuccessful()
            ->assertContent('');

        app(ActiveLibryosManager::class)->activate($user, $libryo);

        $this->get(route('my.applicability-reference.index', ['reference' => $reference->id]))
            ->assertSuccessful()
            ->assertSee('The reason this requirement is in this Libryo')
            ->assertSee($location->title)
            ->assertSee($question->question)
            ->assertSee($legalDomain->title)
            ->assertDontSee($noQuestion->question)
            ->assertDontSee($nonLibryoLegalDomain->title);
    }
}
