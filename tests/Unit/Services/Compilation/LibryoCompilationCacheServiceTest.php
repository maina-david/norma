<?php

namespace Tests\Unit\Services\Compilation;

use App\Enums\Compilation\ContextQuestionAnswer;
use App\Models\Auth\User;
use App\Models\Compilation\ContextQuestion;
use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use App\Services\Compilation\LibryoCompilationCacheService;
use Tests\Feature\My\MyTestCase;

class LibryoCompilationCacheServiceTest extends MyTestCase
{
    public function testHandleLibryoRecompilationAutocompiled(): void
    {
        [$libryo, $organisation, $work, $requirementsLocation, $domain, $tag] = $this->initCompiledStream();

        $this->assertTrue($libryo->references->contains($work->references->first()));

        $libryo->references()->detach();
        $libryo->works()->detach();

        $libryo->refresh();

        $this->assertFalse($libryo->references->contains($work->references->first()));
        $this->assertFalse($libryo->works->contains($work));

        $otherParentWork = Work::factory()->create();
        $work->children->first()->parents()->attach($otherParentWork);
        $this->assertNull($libryo->compiled_at);
        app(LibryoCompilationCacheService::class)->handleLibryoRecompilation($libryo);
        $this->assertNotNull($libryo->compiled_at);

        $libryo->refresh();

        $this->assertTrue($libryo->references->contains($work->references->first()));

        $this->assertTrue($libryo->works->contains($work));
        $this->assertTrue($libryo->works->contains($work->children->first()));
        // also parent works that don't have references compiled should be in the place_work cache
        $this->assertTrue($libryo->works->contains($otherParentWork));

        $reference = Reference::factory()->create();
        $question = ContextQuestion::factory()->create();
        $reference->locations()->attach($requirementsLocation);
        $reference->legalDomains()->attach($domain);
        $reference->contextQuestions()->attach($question);

        $user = User::factory()->create();
        $libryo->contextQuestions()->attach($question, ['answer' => ContextQuestionAnswer::yes()->value]);
        app(LibryoCompilationCacheService::class)->handleLibryoRecompilation($libryo);
        $this->assertTrue($libryo->references()->whereKey($reference->id)->exists());

        $libryo->contextQuestions()->updateExistingPivot($question, ['answer' => ContextQuestionAnswer::no()->value]);
        app(LibryoCompilationCacheService::class)->handleLibryoRecompilation($libryo);
        $this->assertFalse($libryo->references()->whereKey($reference->id)->exists());

        // context question should now be ignored and added anyway, even though the answer is still "no"
        $libryo->compilationSetting->update(['use_context_questions' => false]);
        app(LibryoCompilationCacheService::class)->handleLibryoRecompilation($libryo);
        $this->assertTrue($libryo->references()->whereKey($reference->id)->exists());

        $reference->locations()->detach();
        app(LibryoCompilationCacheService::class)->handleLibryoRecompilation($libryo);
        $this->assertFalse($libryo->references()->whereKey($reference->id)->exists());

        // update setting to not use collections / jurisdictions should now be ignored and added anyway, even though it doesn't have the right collections
        $libryo->compilationSetting->update(['use_collections' => false]);
        app(LibryoCompilationCacheService::class)->handleLibryoRecompilation($libryo);
        $this->assertTrue($libryo->references()->whereKey($reference->id)->exists());

        $reference->legalDomains()->detach($domain);
        app(LibryoCompilationCacheService::class)->handleLibryoRecompilation($libryo);
        $this->assertFalse($libryo->references()->whereKey($reference->id)->exists());
        // update setting to not use legal domains should now be ignored and added anyway, even though it doesn't have the right legal domains
        $libryo->compilationSetting->update(['use_legal_domains' => false]);
        app(LibryoCompilationCacheService::class)->handleLibryoRecompilation($libryo);
        $this->assertTrue($libryo->references()->whereKey($reference->id)->exists());
    }

    public function testHandleLibryoRecompilationManual(): void
    {
        [$libryo, $organisation, $work, $requirementsLocation, $domain, $tag] = $this->initCompiledStream();

        $libryo->library->references()->attach($work->references);
        $libryo->update(['auto_compiled' => false]);

        $this->assertTrue($libryo->references->contains($work->references->first()));
        $libryo->references()->detach();
        $libryo->works()->detach();
        $libryo->refresh();
        $this->assertFalse($libryo->references->contains($work->references->first()));

        app(LibryoCompilationCacheService::class)->handleLibryoRecompilation($libryo);

        $libryo->refresh();

        $this->assertTrue($libryo->references->contains($work->references->first()));
        $this->assertTrue($libryo->works->contains($work));
    }
}
