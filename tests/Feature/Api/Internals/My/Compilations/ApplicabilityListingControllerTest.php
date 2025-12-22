<?php

namespace Tests\Feature\Api\Internals\My\Compilations;

use App\Enums\Compilation\ContextQuestionAnswer;
use App\Models\Compilation\ContextQuestion;
use App\Services\Customer\ActiveNormasManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\My\MyTestCase;
use Tests\Traits\CompilesStream;

class ApplicabilityListingControllerTest extends MyTestCase
{
    use RefreshDatabase;
    use CompilesStream;

    /**
     * Testing applicability sent to the api request.
     *
     * @return void
     */
    public function testApplicabilityListing(): void
    {
        $user = $this->signIn();

        [$norma,$org,$work,$location,$category,$tag,$childWork] = $this->initCompiledStream();
        $reference = $work->references()->first();
        $question = ContextQuestion::factory()->create();

        $question->normas()->attach($norma->id, ['answer' => ContextQuestionAnswer::yes()->value]);
        $question->references()->attach([$reference->id]);

        $this->activateAllStreams($user);
        app(ActiveNormasManager::class)->activate($user, $norma);

        $this->getJson(route('api.my.references.applicability.index', ['reference' => $reference->id]))
            ->assertSuccessful()
            ->assertJson([
                'data' => [
                    'locations' => [
                        $location->title,
                    ],
                    'questions' => [
                        [
                            'id' => $question->id,
                            'title' => $question->question,
                            'hash_id' => $question->hash_id,
                        ],
                    ],
                    'norma' => [
                        'id' => $norma->id,
                        'hash_id' => $norma->hash_id,
                        'title' => $norma->title,
                    ],
                    'categories' => [
                        [
                            'id' => $category->id,
                            'title' => $category->title,
                        ],
                    ],
                    'canManageApplicability' => true,
                ],
            ]);
    }
}
