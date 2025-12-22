<?php

namespace Tests\Feature\Corpus\Settings;

use App\Models\Compilation\Library;
use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use Tests\Feature\Settings\SettingsTestCase;

class ForLibraryWorkControllerTest extends SettingsTestCase
{
    /**
     * @return void
     */
    public function testIndex(): void
    {
        $user = $this->signIn();
        $library = Library::factory()->create();
        $org = Organisation::factory()->create();
        Norma::factory()->for($library)->for($org)->create();
        $route = route('my.settings.works.for.library.index', ['library' => $library->id]);

        $user->organisations()->attach($org, ['is_admin' => true]);

        $response = $this->withExceptionHandling()->get($route);
        $response->assertForbidden();

        $this->signIn($this->mySuperUser());

        $work = Work::factory()->create();
        $reference = Reference::factory()->for($work)->create();
        $work2 = Work::factory()->create();
        $reference2 = Reference::factory()->for($work2)->create();
        $library->references()->attach($reference);

        $response = $this->get($route);
        // when viewing in single organisations mode, you should only see the teams the norma is part of that are in this org
        $response->assertSee($work->title);
        $response->assertDontSee($work2->title);

        $response->assertSee('Generic Compile');
        $response->assertSee('Generic Decompile');

        $route = route('my.settings.works.for.library.index', ['library' => $library->id, 'search' => $work->title]);
        $response = $this->get($route);
        $response->assertSee($work->title);
    }
}
