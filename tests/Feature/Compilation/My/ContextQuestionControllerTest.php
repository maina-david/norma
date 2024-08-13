<?php

namespace Tests\Feature\Compilation\My;

use App\Actions\Compilation\Autocompilation\HandleAutoCompilationExcelReportExport;
use App\Enums\Compilation\ContextQuestionAnswer;
use App\Jobs\Compilation\AutoCompilationExcelImport;
use App\Livewire\Compilation\ContextQuestion\ContextQuestionAnswerToggle;
use App\Models\Auth\Role;
use App\Models\Compilation\ContextQuestion;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Customer\Pivots\ContextQuestionLibryo;
use App\Models\Ontology\Category;
use App\Services\Customer\ActiveLibryosManager;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Feature\Settings\SettingsTestCase;

class ContextQuestionControllerTest extends SettingsTestCase
{
    public function testIndexQuestions(): void
    {
        $routeName = 'my.context-questions.index';
        $user = $this->signIn();
        $user->roles()->attach(Role::factory()->my()->create());

        $org = Organisation::factory()->create();
        $libryos = Libryo::factory(3)->for($org)->create();
        $contextQuestions = ContextQuestion::factory(3)->create();
        $parent = Category::factory()->create(['level' => 1]);
        $category = Category::factory()->create(['parent_id' => $parent->id, 'level' => 2]);
        $contextQuestions[0]->categories()->attach($category->id);
        $libryos[0]->contextQuestions()->attach($contextQuestions->modelKeys(), ['answer' => ContextQuestionAnswer::yes()->value]);
        $libryos[1]->contextQuestions()->attach($contextQuestions->modelKeys(), ['answer' => ContextQuestionAnswer::yes()->value]);
        $libryos[2]->contextQuestions()->attach($contextQuestions->modelKeys(), ['answer' => ContextQuestionAnswer::yes()->value]);

        $libryos[0]->contextQuestions()->updateExistingPivot($contextQuestions[0], ['answer' => ContextQuestionAnswer::no()->value]);
        $libryos[1]->contextQuestions()->updateExistingPivot($contextQuestions[0], ['answer' => ContextQuestionAnswer::no()->value]);
        $libryos[2]->contextQuestions()->updateExistingPivot($contextQuestions[0], ['answer' => ContextQuestionAnswer::no()->value]);
        $user->organisations()->attach($org, ['is_admin' => true]);

        $this->activateAllStreams($user, $org);

        $response = $this->get(route($routeName));
        $response->assertSee($contextQuestions[0]->toQuestion());
        $response->assertSee($contextQuestions[1]->toQuestion());

        $response = $this->get(route($routeName, ['search' => str_replace('?', '', $contextQuestions[0]->toQuestion())]));
        $response->assertSee($contextQuestions[0]->toQuestion());
        $response->assertDontSee($contextQuestions[1]->toQuestion());

        $this->get(route($routeName, ['categories' => [$category->id]]))
            ->assertSuccessful()
            ->assertSee($contextQuestions[0]->toQuestion())
            ->assertDontSee($contextQuestions[1]->toQuestion());

        $this->get(route($routeName, ['answer' => [ContextQuestionAnswer::yes()->value]]))
            ->assertSuccessful()
            ->assertDontSee($contextQuestions[0]->toQuestion())
            ->assertSee($contextQuestions[1]->toQuestion());

        app(ActiveLibryosManager::class)->activate($user, $libryos[0]);

        $this->get(route($routeName, ['answer' => [ContextQuestionAnswer::yes()->value]]))
            ->assertSuccessful()
            ->assertDontSee($contextQuestions[0]->toQuestion())
            ->assertSee($contextQuestions[1]->toQuestion())
            ->assertSeeLivewire(ContextQuestionAnswerToggle::class);

        $answer = ContextQuestionLibryo::where('context_question_id', $contextQuestions[1]->id)
            ->where('place_id', $libryos[0]->id)
            ->first();

        $this->assertSame(ContextQuestionAnswer::yes()->value, $answer->answer);

        Livewire::test(ContextQuestionAnswerToggle::class)
            ->set('answer', $answer->answer)
            ->set('questionId', $contextQuestions[1]->id)
            ->set('libryoId', $libryos[0]->id)
            ->call('changeAnswer', ContextQuestionAnswer::no()->value);

        $answer = ContextQuestionLibryo::where('context_question_id', $contextQuestions[1]->id)
            ->where('place_id', $libryos[0]->id)
            ->first();

        $this->assertSame(ContextQuestionAnswer::no()->value, $answer->answer);
    }

    public function testShowQuestion(): void
    {
        $routeName = 'my.context-questions.show';
        $question = ContextQuestion::factory()->create();

        $user = $this->signIn();
        $user->roles()->attach(Role::factory()->my()->create());

        $org = Organisation::factory()->create();
        $nonOrgLibryo = Libryo::factory()->create();
        $libryos = Libryo::factory(3)->for($org)->create();
        $user->libryos()->attach($libryos->modelKeys());
        $question->libryos()->attach($libryos->modelKeys(), ['answer' => ContextQuestionAnswer::yes()->value]);
        $user->organisations()->attach($org);

        $this->activateAllStreams($user);

        $response = $this->get(route($routeName, ['question' => $question->hash_id, 'activateOrgId' => $org->id]));

        $response->assertSee($libryos[0]->title);
        $response->assertDontSee($nonOrgLibryo->title);

        $this->get(route('my.context-questions.libryo.show', ['question' => $question->hash_id, 'libryo' => $libryos[0]->hash_id]))
            ->assertSuccessful()
            ->assertSee($libryos[0]->title);

        app(ActiveLibryosManager::class)->activate($user, $libryos[0]);
        $this->get(route($routeName, ['question' => $question->hash_id]))
            ->assertRedirect(route('my.context-questions.libryo.show', ['question' => $question->hash_id, 'libryo' => $libryos[0]->hash_id]));
    }

    public function testShowQuestionActions(): void
    {
        $routeName = 'my.context-questions.actions.for.question';
        $question = ContextQuestion::factory()->create();

        $user = $this->signIn();
        $user->roles()->attach(Role::factory()->my()->create());

        $org = Organisation::factory()->create();
        $libryos = Libryo::factory(3)->for($org)->create();
        $question->libryos()->attach($libryos->modelKeys(), ['answer' => ContextQuestionAnswer::yes()->value]);
        $user->organisations()->attach($org);

        $this->activateAllStreams($user);

        $response = $this->withActivatedOrg($org)->followingRedirects()->post(route($routeName, ['question' => $question->id]), [
            'action' => 'applicability_answer_no',
            'actions-checkbox-' . $libryos[0]->id => 'on',
        ])->assertSuccessful();

        $this->assertTrue($libryos[0]->contextQuestions->first()->pivot->answer === ContextQuestionAnswer::no()->value);
        $response = $this->withActivatedOrg($org)->followingRedirects()->post(route($routeName, ['question' => $question->id]), [
            'action' => 'applicability_answer_yes',
            'actions-checkbox-' . $libryos[0]->id => 'on',
        ])->assertSuccessful();
        $this->assertTrue($libryos[0]->contextQuestions()->first()->pivot->answer === ContextQuestionAnswer::yes()->value);
        $this->assertTrue($libryos[0]->contextQuestions()->first()->pivot->last_answered_by === $user->id);
    }

    public function testIndexActions(): void
    {
        $routeName = 'my.context-questions.actions';
        $question = ContextQuestion::factory()->create();

        $user = $this->signIn();
        $user->roles()->attach(Role::factory()->my()->create());

        $org = Organisation::factory()->create();
        $libryos = Libryo::factory(3)->for($org)->create();
        $libryoOther = Libryo::factory()->create();
        $question->libryos()->attach($libryos->modelKeys(), ['answer' => ContextQuestionAnswer::yes()->value]);
        $question->libryos()->attach($libryoOther->id, ['answer' => ContextQuestionAnswer::yes()->value]);
        $user->organisations()->attach($org, ['is_admin' => true]);

        $this->activateAllStreams($user);

        $this->withActivatedOrg($org)->followingRedirects()->post(route($routeName, ['question' => $question->hash_id]), [
            'action' => 'applicability_answer_no',
            'actions-checkbox-' . $question->id => 'on',
        ])->assertSuccessful();

        $this->withActivatedOrg($org)->followingRedirects()->post(route($routeName, ['question' => $question->hash_id]), [
            'action' => 'applicability_answer_no',
            'actions-checkbox-' . $question->id => 'on',
            'action-validated' => Hash::make(1),
            'context_hide_duplicate_notice' => true,
        ])->assertSuccessful();

        $this->withActivatedOrg($org)->followingRedirects()->post(route($routeName, ['question' => $question->hash_id]), [
            'action' => 'applicability_answer_no',
            'actions-checkbox-' . $question->id => 'on',
        ])->assertSuccessful();

        $this->assertTrue($libryos[0]->contextQuestions->first()->pivot->answer === ContextQuestionAnswer::no()->value);
        // make sure other libryo from other org doesn't get answered by mistake
        $this->assertFalse($libryoOther->contextQuestions->first()->pivot->answer === ContextQuestionAnswer::no()->value);
        $response = $this->withActivatedOrg($org)->followingRedirects()->post(route($routeName, ['question' => $question->hash_id]), [
            'action' => 'applicability_answer_yes',
            'actions-checkbox-' . $question->id => 'on',
        ])->assertSuccessful();
        $this->assertTrue($libryos[0]->contextQuestions()->first()->pivot->answer === ContextQuestionAnswer::yes()->value);
    }

    public function testImport(): void
    {
        $routeName = 'my.compilation.context-questions.import';
        $user = $this->signIn();
        $user->roles()->attach(Role::factory()->my()->create());

        $org = Organisation::factory()->create();
        $user->organisations()->attach($org, ['is_admin' => true]);
        $this->activateAllStreams($user);

        $response = $this->withActivatedOrg($org)->followingRedirects()->get(route($routeName))->assertSuccessful();
    }

    public function testUploadExcelImport(): void
    {
        Storage::fake();
        Queue::fake();
        $routeName = 'my.compilation.context-questions.import.upload';
        $user = $this->signIn();
        $user->roles()->attach(Role::factory()->my()->create());

        $org = Organisation::factory()->create();
        $user->organisations()->attach($org, ['is_admin' => true]);
        $this->activateAllStreams($user);

        $file = UploadedFile::fake()->create('test.xlsx', 'application/vnd.ms-excel');
        $response = $this->withActivatedOrg($org)->followingRedirects()->post(route($routeName), ['file' => $file])
            ->assertSuccessful();

        Queue::assertPushed(AutoCompilationExcelImport::class);

        // without a file should get error message
        $response = $this->withActivatedOrg($org)->post(route($routeName))
            ->assertSessionHas('flash.type', 'error');
    }

    public function testExport(): void
    {
        Storage::fake();
        Queue::fake();
        $routeName = 'my.compilation.context-questions.export';
        $user = $this->signIn();
        $user->roles()->attach(Role::factory()->my()->create());

        $org = Organisation::factory()->create();
        $user->organisations()->attach($org, ['is_admin' => true]);
        Libryo::factory()->for($org)->create();
        $this->activateAllStreams($user, $org);

        $response = $this->withActivatedOrg($org)->followingRedirects()->get(route($routeName))->assertSuccessful();
        HandleAutoCompilationExcelReportExport::assertPushed();
    }

    public function testLibryoActions(): void
    {
        $question = ContextQuestion::factory()->create();
        $org = Organisation::factory()->create();
        $libryo = Libryo::factory()->for($org)->create();
        $libryo->contextQuestions()->attach($question->id, ['answer' => ContextQuestionAnswer::yes()->value]);

        $routeParams = ['question' => $question->hash_id, 'libryo' => $libryo->id];
        $from = route('my.context-questions.libryo.show', $routeParams);
        $target = route('my.context-questions.libryo.answer', $routeParams);

        $user = $this->signIn();
        $user->roles()->attach(Role::factory()->my()->create());

        $user->libryos()->attach($libryo->id);
        $user->organisations()->attach($org, ['is_admin' => true]);

        $this->activateAllStreams($user);

        $this->assertDatabaseMissing(ContextQuestionLibryo::class, [
            'context_question_id' => $question->id,
            'place_id' => $libryo->id,
            'answer' => ContextQuestionAnswer::no()->value,
        ]);

        $this->from($from)
            ->put($target, ['answer' => ContextQuestionAnswer::no()->value])
            ->assertSessionDoesntHaveErrors()
            ->assertRedirect($from);

        $this->assertDatabaseHas(ContextQuestionLibryo::class, [
            'context_question_id' => $question->id,
            'place_id' => $libryo->id,
            'answer' => ContextQuestionAnswer::no()->value,
        ]);
    }
}
