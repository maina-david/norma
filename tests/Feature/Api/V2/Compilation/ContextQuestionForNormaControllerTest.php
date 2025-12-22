<?php

namespace Tests\Feature\Api\V2\Compilation;

use App\Enums\Compilation\ContextQuestionAnswer;
use App\Models\Auth\User;
use App\Models\Compilation\ContextQuestion;
use App\Models\Customer\Norma;
use App\Models\Customer\Pivots\ContextQuestionNorma;
use App\Models\Geonames\Location;
use Laravel\Passport\Passport;
use Tests\Feature\Api\ApiTestCase;

class ContextQuestionForNormaControllerTest extends ApiTestCase
{
    /**
     * @return void
     */
    public function testItRendersTheCorrectItems(): void
    {
        $location = Location::factory()->create();
        $location->location_country_id = $location->id;
        $location->save();

        $norma = Norma::factory()->create(['location_id' => $location->id]);
        $user = User::factory()->create();
        $user->normas()->attach($norma);

        $questions = ContextQuestion::factory()->count(2)->create();
        $unattachedQuestions = Norma::factory()->count(4)->create();

        $described = $questions->first();

        $described->descriptions()->create(['description' => 'No Location']);
        $described->descriptions()->create([
            'location_id' => $location->location_country_id,
            'description' => 'With Location',
        ]);

        $questions->each(fn ($question) => $question->normas()->attach($norma, [
            'answer' => ContextQuestionAnswer::no()->value,
        ]));

        $payload = $norma->contextQuestions()->get()->map(fn ($question) => [
            'id' => $question->id,
            'question' => $question->toQuestion(),
            'category_id' => $question->category_id,
            'description' => $question->id === $described->id ? 'With Location' : null,
            'answer' => $question->pivot->answer,
        ])->toArray();

        $routeName = 'api.v2.context-questions.norma.index';
        $response = $this->assertApiUnauthorizedThenRun($user, 'get', route($routeName, ['norma' => $norma]))
            ->assertSuccessful()
            ->assertJson(['data' => $payload], true);

        $unattachedQuestions->each(fn ($question) => $response->assertJsonMissing([
            'data' => ['id' => $question->id],
        ]));

        $meta = [
            'current_page' => 1,
            'from' => 1,
            'last_page' => 2,
            'per_page' => 1,
            'to' => 1,
            'total' => 2,
        ];
        $links = [
            'first' => route($routeName, ['norma' => $norma, 'perPage' => 1, 'page' => 1]),
            'last' => route($routeName, ['norma' => $norma, 'perPage' => 1, 'page' => 2]),
            'prev' => null,
            'next' => route($routeName, ['norma' => $norma, 'perPage' => 1, 'page' => 2]),
        ];

        $this->json('get', route($routeName, ['norma' => $norma, 'perPage' => 1, 'page' => 1]))
            ->assertSuccessful()
            ->assertJson(['data' => [$payload[0]], 'meta' => $meta, 'links' => $links])
            ->assertJsonMissing(['data' => [$payload[1]]]);
    }

    /**
     * @return void
     */
    public function testItAnswersCorrectQuestions(): void
    {
        $norma = Norma::factory()->create();
        $user = User::factory()->create();
        $user->normas()->attach($norma);
        $questions = ContextQuestion::factory()->count(2)->create();
        $unattachedQuestions = ContextQuestion::factory()->count(4)->create();

        $questions->each(fn ($question) => $question->normas()->attach($norma, [
            'answer' => ContextQuestionAnswer::maybe()->value,
        ]));

        $table = (new ContextQuestionNorma())->getTable();

        $unattachedQuestions->each(fn ($question) => $this->assertDatabaseMissing($table, [
            'context_question_id' => $question->id,
            'place_id' => $norma->id,
            'answer' => ContextQuestionAnswer::maybe()->value,
        ]));

        $target = 'api.v2.context-questions.norma.answer.store';

        Passport::actingAs($user);

        $questions->each(function ($question) use ($table, $norma, $target) {
            $this->assertDatabaseHas($table, [
                'context_question_id' => $question->id,
                'place_id' => $norma->id,
                'answer' => ContextQuestionAnswer::maybe()->value,
            ]);

            // Test invalid answer.
            $route = route($target, ['question' => $question->id, 'norma' => $norma->id, 'answer' => 'zebracorn']);

            $this->withExceptionHandling()
                ->json('post', $route, [])
                ->assertUnprocessable();

            $this->assertDatabaseHas($table, [
                'context_question_id' => $question->id,
                'place_id' => $norma->id,
                'answer' => ContextQuestionAnswer::maybe()->value,
            ]);

            // Test changing to no.
            $route = route($target, [
                'question' => $question->id,
                'norma' => $norma->id,
                'answer' => ContextQuestionAnswer::no()->value,
            ]);

            $this->json('post', $route, [])
                ->assertSuccessful()
                ->assertJson([], true);

            $this->assertDatabaseHas($table, [
                'context_question_id' => $question->id,
                'place_id' => $norma->id,
                'answer' => ContextQuestionAnswer::no()->value,
            ]);

            // Test reseting
            $route = route($target, [
                'question' => $question->id,
                'norma' => $norma->id,
                'answer' => 'reset',
            ]);

            $this->json('post', $route, [])
                ->assertSuccessful()
                ->assertJson([], true);

            $this->assertDatabaseHas($table, [
                'context_question_id' => $question->id,
                'place_id' => $norma->id,
                'answer' => ContextQuestionAnswer::yes()->value,
            ]);
        });

        $unattachedQuestions->each(function ($question) use ($table, $norma, $target) {
            $this->assertDatabaseMissing($table, ['context_question_id' => $question->id, 'place_id' => $norma->id]);

            // Test invalid answer with unattached question.
            $route = route($target, ['question' => $question->id, 'norma' => $norma->id, 'answer' => 'zebracorn']);

            $this->withExceptionHandling()
                ->json('post', $route, [])
                ->assertNotFound();

            $this->assertDatabaseMissing($table, ['context_question_id' => $question->id, 'place_id' => $norma->id]);

            // Test no with unattached question
            $route = route($target, [
                'question' => $question->id,
                'norma' => $norma->id,
                'answer' => ContextQuestionAnswer::maybe()->value,
            ]);

            $this->withExceptionHandling()
                ->json('post', $route, [])
                ->assertNotFound();

            $this->assertDatabaseMissing($table, ['context_question_id' => $question->id, 'place_id' => $norma->id]);

            // Test yes with unattached question
            $route = route($target, [
                'question' => $question->id,
                'norma' => $norma->id,
                'answer' => 'reset',
            ]);

            $this->withExceptionHandling()
                ->json('post', $route, [])
                ->assertNotFound();

            $this->assertDatabaseMissing($table, ['context_question_id' => $question->id, 'place_id' => $norma->id]);
        });
    }
}
