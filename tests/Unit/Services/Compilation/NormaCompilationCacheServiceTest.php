<?php

namespace Tests\Unit\Services\Compilation;

use App\Enums\Compilation\ContextQuestionAnswer;
use App\Models\Auth\User;
use App\Models\Compilation\ContextQuestion;
use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use App\Services\Compilation\NormaCompilationCacheService;
use Tests\Feature\My\MyTestCase;

class NormaCompilationCacheServiceTest extends MyTestCase
{
    public function testHandleNormaRecompilationAutocompiled(): void
    {
        [$norma, $organisation, $work, $requirementsLocation, $domain, $tag] = $this->initCompiledStream();

        $this->assertTrue($norma->references->contains($work->references->first()));

        $norma->references()->detach();
        $norma->works()->detach();

        $norma->refresh();

        $this->assertFalse($norma->references->contains($work->references->first()));
        $this->assertFalse($norma->works->contains($work));

        $otherParentWork = Work::factory()->create();
        $work->children->first()->parents()->attach($otherParentWork);
        $this->assertNull($norma->compiled_at);
        app(NormaCompilationCacheService::class)->handleNormaRecompilation($norma);
        $this->assertNotNull($norma->compiled_at);

        $norma->refresh();

        $this->assertTrue($norma->references->contains($work->references->first()));

        $this->assertTrue($norma->works->contains($work));
        $this->assertTrue($norma->works->contains($work->children->first()));
        // also parent works that don't have references compiled should be in the place_work cache
        $this->assertTrue($norma->works->contains($otherParentWork));

        $reference = Reference::factory()->create();
        $question = ContextQuestion::factory()->create();
        $reference->locations()->attach($requirementsLocation);
        $reference->legalDomains()->attach($domain);
        $reference->contextQuestions()->attach($question);

        $user = User::factory()->create();
        $norma->contextQuestions()->attach($question, ['answer' => ContextQuestionAnswer::yes()->value]);
        app(NormaCompilationCacheService::class)->handleNormaRecompilation($norma);
        $this->assertTrue($norma->references()->whereKey($reference->id)->exists());

        $norma->contextQuestions()->updateExistingPivot($question, ['answer' => ContextQuestionAnswer::no()->value]);
        app(NormaCompilationCacheService::class)->handleNormaRecompilation($norma);
        $this->assertFalse($norma->references()->whereKey($reference->id)->exists());

        // context question should now be ignored and added anyway, even though the answer is still "no"
        $norma->compilationSetting->update(['use_context_questions' => false]);
        app(NormaCompilationCacheService::class)->handleNormaRecompilation($norma);
        $this->assertTrue($norma->references()->whereKey($reference->id)->exists());

        $reference->locations()->detach();
        app(NormaCompilationCacheService::class)->handleNormaRecompilation($norma);
        $this->assertFalse($norma->references()->whereKey($reference->id)->exists());

        // update setting to not use collections / jurisdictions should now be ignored and added anyway, even though it doesn't have the right collections
        $norma->compilationSetting->update(['use_collections' => false]);
        app(NormaCompilationCacheService::class)->handleNormaRecompilation($norma);
        $this->assertTrue($norma->references()->whereKey($reference->id)->exists());

        $reference->legalDomains()->detach($domain);
        app(NormaCompilationCacheService::class)->handleNormaRecompilation($norma);
        $this->assertFalse($norma->references()->whereKey($reference->id)->exists());
        // update setting to not use legal domains should now be ignored and added anyway, even though it doesn't have the right legal domains
        $norma->compilationSetting->update(['use_legal_domains' => false]);
        app(NormaCompilationCacheService::class)->handleNormaRecompilation($norma);
        $this->assertTrue($norma->references()->whereKey($reference->id)->exists());
    }

    public function testHandleNormaRecompilationManual(): void
    {
        [$norma, $organisation, $work, $requirementsLocation, $domain, $tag] = $this->initCompiledStream();

        $norma->library->references()->attach($work->references);
        $norma->update(['auto_compiled' => false]);

        $this->assertTrue($norma->references->contains($work->references->first()));
        $norma->references()->detach();
        $norma->works()->detach();
        $norma->refresh();
        $this->assertFalse($norma->references->contains($work->references->first()));

        app(NormaCompilationCacheService::class)->handleNormaRecompilation($norma);

        $norma->refresh();

        $this->assertTrue($norma->references->contains($work->references->first()));
        $this->assertTrue($norma->works->contains($work));
    }
}
