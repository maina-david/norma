<?php

namespace Tests\Feature\Corpus\Collaborate;

use App\Models\Actions\ActionArea;
use App\Models\Compilation\ContextQuestion;
use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use App\Models\Geonames\Location;
use App\Models\Ontology\LegalDomain;
use Tests\Feature\Traits\HasVisibleFieldAssertions;
use Tests\TestCase;

class RequirementsPreviewControllerTest extends TestCase
{
    use HasVisibleFieldAssertions;

    /**
     * The list of database columns that should be visible on the pages.
     *
     * @return string[]
     */
    protected static function visibleFields(): array
    {
        return ['title'];
    }

    public function testItRendersTheIndexPage(): void
    {
        $routeName = 'collaborate.corpus.requirements.preview.index';
        $route = route($routeName);
        $this->validateAuthGuard($route);
        $this->collaboratorSignIn();
        $this->validateCollaborateRole($route);
        $response = $this->get($route)->assertSuccessful();

        $location = Location::factory()->create();
        $domain = LegalDomain::factory()->create();
        $work = Work::factory()->has(Reference::factory()->count(3))->create();
        $childWork = Work::factory()->has(Reference::factory()->count(3))->create();
        $childWork->parents()->attach($work);
        $actionArea = ActionArea::factory()->create();
        $question = ContextQuestion::factory()->create();

        $work->references->load('refPlainText');
        $childWork->references->load('refPlainText');

        $work->references[0]->locations()->attach($location);
        $work->references[0]->legalDomains()->attach($domain);
        $work->references[0]->actionAreas()->attach($actionArea);
        $work->references[0]->contextQuestions()->attach($question);

        $childWork->references[0]->locations()->attach($location);
        $childWork->references[0]->legalDomains()->attach($domain);
        $childWork->references[0]->actionAreas()->attach($actionArea);
        $childWork->references[0]->contextQuestions()->attach($question);

        $response = $this->get(route($routeName, ['no_questions' => 'yes']))
            ->assertSuccessful()
            ->assertSee($work->references[1]->refPlainText->plain_text)
            ->assertDontSee($work->references[0]->refPlainText->plain_text)
            ->assertSee($childWork->references[1]->refPlainText->plain_text)
            ->assertDontSee($childWork->references[0]->refPlainText->plain_text);
        $response = $this->get(route($routeName, ['search' => $work->title]))
            ->assertSee($work->title);
    }

    public function testItRendersTheShowPage(): void
    {
        $location = Location::factory()->create();
        $domain = LegalDomain::factory()->create();
        $work = Work::factory()->has(Reference::factory()->count(3))->create();
        $childWork = Work::factory()->has(Reference::factory()->count(3))->create();
        $childWork->parents()->attach($work);
        $actionArea = ActionArea::factory()->create();
        $question = ContextQuestion::factory()->create();
        $reference = $work->references[0];
        $reference->locations()->attach($location);
        $reference->legalDomains()->attach($domain);
        $reference->actionAreas()->attach($actionArea);
        $reference->contextQuestions()->attach($question);

        $work->references->load('refPlainText');
        $childWork->references->load('refPlainText');

        $routeName = 'collaborate.corpus.requirements.preview.reference.show';
        $route = route($routeName, ['reference' => $reference->id]);
        $this->validateAuthGuard($route);
        $this->collaboratorSignIn();
        $this->validateCollaborateRole($route);

        $response = $this->get($route)
            ->assertSuccessful()
            ->assertSee($reference->refPlainText->plain_text)
            ->assertSee($domain->title)
            ->assertSee($actionArea->title)
            ->assertSee($question->toQuestion())
            ->assertSee($location->title);
    }
}
