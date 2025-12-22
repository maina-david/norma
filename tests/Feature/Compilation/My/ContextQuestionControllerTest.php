<?php

namespace Tests\Feature\Compilation\My;

use App\Actions\Compilation\Autocompilation\HandleAutoCompilationExcelReportExport;
use App\Enums\Compilation\ContextQuestionAnswer;
use App\Jobs\Compilation\AutoCompilationExcelImport;
use App\Livewire\Compilation\ContextQuestion\ContextQuestionAnswerToggle;
use App\Models\Auth\Role;
use App\Models\Compilation\ContextQuestion;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Customer\Pivots\ContextQuestionNorma;
use App\Models\Ontology\Category;
use App\Services\Customer\ActiveNormasManager;
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
        $normas = Norma::factory(3)->for($org)->create();
        $contextQuestions = ContextQuestion::factory(3)->create();
        $parent = Category::factory()->create(['level' => 1]);
        $category = Category::factory()->create(['parent_id' => $parent->id, 'level' => 2]);
        $contextQuestions[0]->categories()->attach($category->id);
        $normas[0]->contextQuestions()->attach($contextQuestions->modelKeys(), ['answer' => ContextQuestionAnswer::yes()->value]);
        $normas[1]->contextQuestions()->attach($contextQuestions->modelKeys(), ['answer' => ContextQuestionAnswer::yes()->value]);
        $normas[2]->contextQuestions()->attach($contextQuestions->modelKeys(), ['answer' => ContextQuestionAnswer::yes()->value]);

        $normas[0]->contextQuestions()->updateExistingPivot($contextQuestions[0], ['answer' => ContextQuestionAnswer::no()->value]);
        $normas[1]->contextQuestions()->updateExistingPivot($contextQuestions[0], ['answer' => ContextQuestionAnswer::no()->value]);
        $normas[2]->contextQuestions()->updateExistingPivot($contextQuestions[0], ['answer' => ContextQuestionAnswer::no()->value]);
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

        app(ActiveNormasManager::class)->activate($user, $normas[0]);

        $this->get(route($routeName, ['answer' => [ContextQuestionAnswer::yes()->value]]))
            ->assertSuccessful()
            ->assertDontSee($contextQuestions[0]->toQuestion())
            ->assertSee($contextQuestions[1]->toQuestion())
            ->assertSeeLivewire(ContextQuestionAnswerToggle::class);

        $answer = ContextQuestionNorma::where('context_question_id', $contextQuestions[1]->id)
            ->where('place_id', $normas[0]->id)
            ->first();

        $this->assertSame(ContextQuestionAnswer::yes()->value, $answer->answer);

        Livewire::test(ContextQuestionAnswerToggle::class)
            ->set('answer', $answer->answer)
            ->set('questionId', $contextQuestions[1]->id)
            ->set('normaId', $normas[0]->id)
            ->call('changeAnswer', ContextQuestionAnswer::no()->value);

        $answer = ContextQuestionNorma::where('context_question_id', $contextQuestions[1]->id)
            ->where('place_id', $normas[0]->id)
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
        $nonOrgNorma = Norma::factory()->create();
        $normas = Norma::factory(3)->for($org)->create();
        $user->normas()->attach($normas->modelKeys());
        $question->normas()->attach($normas->modelKeys(), ['answer' => ContextQuestionAnswer::yes()->value]);
        $user->organisations()->attach($org);

        $this->activateAllStreams($user);

        $response = $this->get(route($routeName, ['question' => $question->hash_id, 'activateOrgId' => $org->id]));

        $response->assertSee($normas[0]->title);
        $response->assertDontSee($nonOrgNorma->title);

        $this->get(route('my.context-questions.norma.show', ['question' => $question->hash_id, 'norma' => $normas[0]->hash_id]))
            ->assertSuccessful()
            ->assertSee($normas[0]->title);

        app(ActiveNormasManager::class)->activate($user, $normas[0]);
        $this->get(route($routeName, ['question' => $question->hash_id]))
            ->assertRedirect(route('my.context-questions.norma.show', ['question' => $question->hash_id, 'norma' => $normas[0]->hash_id]));
    }

    public function testShowQuestionActions(): void
    {
        $routeName = 'my.context-questions.actions.for.question';
        $question = ContextQuestion::factory()->create();

        $user = $this->signIn();
        $user->roles()->attach(Role::factory()->my()->create());

        $org = Organisation::factory()->create();
        $normas = Norma::factory(3)->for($org)->create();
        $question->normas()->attach($normas->modelKeys(), ['answer' => ContextQuestionAnswer::yes()->value]);
        $user->organisations()->attach($org);

        $this->activateAllStreams($user);

        $response = $this->withActivatedOrg($org)->followingRedirects()->post(route($routeName, ['question' => $question->id]), [
            'action' => 'applicability_answer_no',
            'actions-checkbox-' . $normas[0]->id => 'on',
        ])->assertSuccessful();

        $this->assertTrue($normas[0]->contextQuestions->first()->pivot->answer === ContextQuestionAnswer::no()->value);
        $response = $this->withActivatedOrg($org)->followingRedirects()->post(route($routeName, ['question' => $question->id]), [
            'action' => 'applicability_answer_yes',
            'actions-checkbox-' . $normas[0]->id => 'on',
        ])->assertSuccessful();
        $this->assertTrue($normas[0]->contextQuestions()->first()->pivot->answer === ContextQuestionAnswer::yes()->value);
        $this->assertTrue($normas[0]->contextQuestions()->first()->pivot->last_answered_by === $user->id);
    }

    public function testIndexActions(): void
    {
        $routeName = 'my.context-questions.actions';
        $question = ContextQuestion::factory()->create();

        $user = $this->signIn();
        $user->roles()->attach(Role::factory()->my()->create());

        $org = Organisation::factory()->create();
        $normas = Norma::factory(3)->for($org)->create();
        $normaOther = Norma::factory()->create();
        $question->normas()->attach($normas->modelKeys(), ['answer' => ContextQuestionAnswer::yes()->value]);
        $question->normas()->attach($normaOther->id, ['answer' => ContextQuestionAnswer::yes()->value]);
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

        $this->assertTrue($normas[0]->contextQuestions->first()->pivot->answer === ContextQuestionAnswer::no()->value);
        // make sure other norma from other org doesn't get answered by mistake
        $this->assertFalse($normaOther->contextQuestions->first()->pivot->answer === ContextQuestionAnswer::no()->value);
        $response = $this->withActivatedOrg($org)->followingRedirects()->post(route($routeName, ['question' => $question->hash_id]), [
            'action' => 'applicability_answer_yes',
            'actions-checkbox-' . $question->id => 'on',
        ])->assertSuccessful();
        $this->assertTrue($normas[0]->contextQuestions()->first()->pivot->answer === ContextQuestionAnswer::yes()->value);
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
        Norma::factory()->for($org)->create();
        $this->activateAllStreams($user, $org);

        $response = $this->withActivatedOrg($org)->followingRedirects()->get(route($routeName))->assertSuccessful();
        HandleAutoCompilationExcelReportExport::assertPushed();
    }

    public function testNormaActions(): void
    {
        $question = ContextQuestion::factory()->create();
        $org = Organisation::factory()->create();
        $norma = Norma::factory()->for($org)->create();
        $norma->contextQuestions()->attach($question->id, ['answer' => ContextQuestionAnswer::yes()->value]);

        $routeParams = ['question' => $question->hash_id, 'norma' => $norma->id];
        $from = route('my.context-questions.norma.show', $routeParams);
        $target = route('my.context-questions.norma.answer', $routeParams);

        $user = $this->signIn();
        $user->roles()->attach(Role::factory()->my()->create());

        $user->normas()->attach($norma->id);
        $user->organisations()->attach($org, ['is_admin' => true]);

        $this->activateAllStreams($user);

        $this->assertDatabaseMissing(ContextQuestionNorma::class, [
            'context_question_id' => $question->id,
            'place_id' => $norma->id,
            'answer' => ContextQuestionAnswer::no()->value,
        ]);

        $this->from($from)
            ->put($target, ['answer' => ContextQuestionAnswer::no()->value])
            ->assertSessionDoesntHaveErrors()
            ->assertRedirect($from);

        $this->assertDatabaseHas(ContextQuestionNorma::class, [
            'context_question_id' => $question->id,
            'place_id' => $norma->id,
            'answer' => ContextQuestionAnswer::no()->value,
        ]);
    }
}
