<?php

namespace Tests\Feature\Corpus;

use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceContent;
use App\Models\Corpus\Work;
use Tests\Feature\Settings\SettingsTestCase;

class ForSelectorReferenceControllerTest extends SettingsTestCase
{
    public function testIndex(): void
    {
        $routeName = 'my.settings.corpus.reference.for.selector.index';
        $work = Work::factory()->has(Reference::factory())->create();
        $route = route($routeName, ['work' => $work->id]);
        $user = $this->assertForbiddenForNonAdmin($route, 'get');
        $this->signIn($this->mySuperUser($user));

        $work->references->load('refPlainText');
        $this->get($route)
            ->assertSuccessful()
            ->assertSee($work->references->first()->refPlainText?->plain_text);

        $route = route($routeName, ['work' => $work->id, 'search' => $work->references->first()->refPlainText?->plain_text]);
        $this->get($route)
            ->assertSuccessful()
            ->assertSee($work->references->first()->refPlainText?->plain_text);
    }

    public function testContent(): void
    {
        $content = $this->faker->paragraph();
        $reference = Reference::factory()->create();
        $reference->htmlContent()->save(new ReferenceContent([
            'reference_id' => $reference->id,
            'cached_content' => $content,
        ]));
        $route = route('my.settings.corpus.reference.for.selector.content', ['reference' => $reference->id]);
        $user = $this->assertForbiddenForNonAdmin($route, 'get');
        $this->signIn($this->mySuperUser($user));

        $this->get($route)
            ->assertSuccessful()
            ->assertSee($content);
    }
}
