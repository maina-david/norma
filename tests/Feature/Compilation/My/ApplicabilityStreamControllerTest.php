<?php

namespace Tests\Feature\Compilation\My;

use App\Enums\Compilation\ApplicabilityActivityType;
use App\Enums\Corpus\ReferenceType;
use App\Models\Actions\ActionArea;
use App\Models\Assess\AssessmentItem;
use App\Models\Auth\User;
use App\Models\Compilation\ApplicabilityActivity;
use App\Models\Compilation\ContextQuestion;
use App\Models\Corpus\Reference;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Geonames\Location;
use App\Models\Ontology\LegalDomain;
use Tests\Feature\Settings\SettingsTestCase;

class ApplicabilityStreamControllerTest extends SettingsTestCase
{
    protected function createBase(): array
    {
        $user = $this->signIn();
        $org = Organisation::factory()->create();
        $norma = Norma::factory()->for($org)->create();
        $question = ContextQuestion::factory()->create();

        $user->normas()->attach($norma->id);
        $user->organisations()->attach($org, ['is_admin' => true]);
        $question->normas()->attach($norma->id);

        $this->activateAllStreams($user);

        return [$org, $norma, $question, $user];
    }

    public function testFetchingReferencesAssessmentsAndActions(): void
    {
        /** @var Norma $norma */
        [$org, $norma, $question] = $this->createBase();
        $location = Location::factory()->create();
        $norma->update(['location_id' => $location->id]);
        $norma->updateSetting('use_legal_domains', true);
        $norma->updateSetting('use_collections', true);

        $domain = LegalDomain::factory()->create();
        $domain->update(['top_parent_id' => $domain->id]);
        $norma->legalDomains()->attach($domain->id);

        /** @var Reference $reference */
        $nonNormaReference = Reference::factory()->create(['type' => ReferenceType::citation()->value]);
        $reference = Reference::factory()->create(['type' => ReferenceType::citation()->value]);
        $reference->locations()->attach($location->id);
        $reference->legalDomains()->attach($domain->id);

        $question->references()->attach([$nonNormaReference->id, $reference->id]);

        $this->get(route('my.references.for.context-questions.index', ['question' => $question->id, 'norma' => $norma->id]))
            ->assertSuccessful()
            ->assertSee($reference->refPlainText->plain_text)
            ->assertDontSee($nonNormaReference->refPlainText->plain_text);

        $assessment = AssessmentItem::factory()->create();
        $nonNormaAssessment = AssessmentItem::factory()->create();

        $reference->assessmentItems()->attach($assessment->id);

        $this->get(route('my.assessment-items.for.context-questions.index', ['question' => $question->id, 'norma' => $norma->id]))
            ->assertSuccessful()
            ->assertSee($assessment->description)
            ->assertDontSee($nonNormaAssessment->description);

        $action = ActionArea::factory()->create();
        $nonNormaAction = ActionArea::factory()->create();

        $reference->actionAreas()->attach($action->id);

        $this->get(route('my.action-areas.for.context-questions.index', ['question' => $question->id, 'norma' => $norma->id]))
            ->assertSuccessful()
            ->assertSee($action->description)
            ->assertDontSee($nonNormaAction->description);
    }

    public function testFetchingActivities(): void
    {
        [$org, $norma, $question, $user] = $this->createBase();

        $inNorma = ApplicabilityActivity::factory()->create(['activity_type' => ApplicabilityActivityType::ANSWER_CHANGED->value, 'place_id' => $norma->id, 'context_question_id' => $question->id, 'user_id' => $user->id]);
        $notInNorma = ApplicabilityActivity::factory()->create(['activity_type' => ApplicabilityActivityType::ANSWER_CHANGED->value, 'context_question_id' => $question->id]);

        $nonOrgUser = User::whereKey($notInNorma->user_id)->first();

        $this->get(route('my.activities.for.context-questions.index', ['question' => $question->id, 'norma' => $norma->id]))
            ->assertSuccessful()
            ->assertSee('changed the answer from')
            ->assertSee($user->full_name)
            ->assertDontSee($nonOrgUser->full_name);
    }

    public function testFetchingTasks(): void
    {
        [$org, $norma, $question] = $this->createBase();
        $NonOrgnorma = Norma::factory()->create();
        $task = $question->tasks()->create(['title' => 'In Norma Task', 'place_id' => $norma->id]);
        $nonTask = $question->tasks()->create(['title' => 'Not Norma Task', 'place_id' => $NonOrgnorma->id]);

        $this->get(route('my.tasks.for.context-questions.index', ['question' => $question->id, 'norma' => $norma->id]))
            ->assertSuccessful()
            ->assertSee($task->title)
            ->assertDontSee($nonTask->title);
    }

    public function testFetchingComments(): void
    {
        [$org, $norma, $question] = $this->createBase();
        $this->get(route('my.comments.for.context-questions.index', ['question' => $question->id, 'norma' => $norma->id]))
            ->assertSuccessful();
    }
}
