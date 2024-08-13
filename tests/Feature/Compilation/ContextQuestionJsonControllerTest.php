<?php

namespace Tests\Feature\Compilation;

use App\Models\Compilation\ContextQuestion;
use App\Models\Geonames\Location;
use App\Models\Ontology\Category;
use Tests\TestCase;

class ContextQuestionJsonControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testFetchingContextQuestions(): void
    {
        $category = Category::factory()->create();
        $questions = ContextQuestion::factory()->hasAttached($category)->count(30)->create();
        $location = Location::factory()->create();

        $question = $questions->first();

        $this->signIn();

        $route = route('collaborate.context-questions.json.index', [
            'search' => $question->predicate,
            'categories' => $category->id,
            'location_id' => $location->id,
        ]);

        $this->validateCollaborateRole($route);

        $this->get($route)
            ->assertSuccessful()
            ->assertJson([['id' => $question->id, 'title' => $question->question]]);
    }
}
