<?php

namespace Tests\Feature\Api\Internals\My\Compilations;

use App\Enums\Compilation\ContextQuestionAnswer;
use App\Models\Compilation\ContextQuestion;
use App\Services\Customer\ActiveLibryosManager;
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

        [$libryo,$org,$work,$location,$category,$tag,$childWork] = $this->initCompiledStream();
        $reference = $work->references()->first();
        $question = ContextQuestion::factory()->create();

        $question->libryos()->attach($libryo->id, ['answer' => ContextQuestionAnswer::yes()->value]);
        $question->references()->attach([$reference->id]);

        $this->activateAllStreams($user);
        app(ActiveLibryosManager::class)->activate($user, $libryo);

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
                    'libryo' => [
                        'id' => $libryo->id,
                        'hash_id' => $libryo->hash_id,
                        'title' => $libryo->title,
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
