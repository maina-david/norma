<?php

namespace Tests\Feature\Compilation\Collaborate;

use App\Models\Compilation\ContextQuestion;
use App\Models\Compilation\ContextQuestionDescription;
use Tests\TestCase;

class ForContextQuestionContextQuestionDescriptionControllerTest extends TestCase
{
    public function testIndex(): void
    {
        $cq = ContextQuestion::factory()->create();
        $descriptions = ContextQuestionDescription::factory(3)->for($cq)->create();
        $routeName = 'collaborate.compilation.context-questions.context-question-descriptions.index';
        $route = route($routeName, ['context_question' => $cq]);
        $this->validateAuthGuard($route);
        $this->collaboratorSignIn();
        $this->validateCollaborateRole($route);

        $response = $this->get($route)->assertSuccessful();
        $response->assertSee($descriptions[0]->description);
        $response->assertSee($descriptions[0]->location->title);
    }

    public function testCrud(): void
    {
        $cq = ContextQuestion::factory()->create();
        $routeName = 'collaborate.compilation.context-questions.context-question-descriptions.create';
        $route = route($routeName, ['context_question' => $cq]);
        $this->validateAuthGuard($route);
        $this->collaboratorSignIn();
        $this->validateCollaborateRole($route);

        $this->get($route)
            ->assertSuccessful()
            ->assertSee('Country')
            ->assertSee('Explanation');

        $description = ContextQuestionDescription::factory()->for($cq)->make();
        $routeName = 'collaborate.compilation.context-questions.context-question-descriptions.store';
        $this->followingRedirects()
            ->post(route($routeName, ['context_question' => $cq]), $description->toArray())
            ->assertSuccessful();
        $description = $cq->descriptions->first();

        $routeName = 'collaborate.compilation.context-questions.context-question-descriptions.edit';
        $this->get(route($routeName, ['context_question' => $cq, 'context_question_description' => $description->id]))
            ->assertSuccessful()
            ->assertSee('Country')
            ->assertSee('Explanation');

        $routeName = 'collaborate.compilation.context-questions.context-question-descriptions.update';
        $newText = 'New description';
        $description->description = $newText;
        $this->followingRedirects()
            ->put(route($routeName, ['context_question' => $cq, 'context_question_description' => $description->id]), $description->toArray())
            ->assertSuccessful();
        $this->assertSame($newText, $description->refresh()->description);

        $routeName = 'collaborate.compilation.context-questions.context-question-descriptions.destroy';
        $response = $this->followingRedirects()
            ->delete(route($routeName, ['context_question' => $cq, 'context_question_description' => $description->id]))
            ->assertSuccessful();
    }
}
