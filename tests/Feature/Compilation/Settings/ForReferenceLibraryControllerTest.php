<?php

namespace Tests\Feature\Compilation\Settings;

use App\Models\Compilation\Library;
use App\Models\Corpus\Reference;
use Tests\Feature\Settings\SettingsTestCase;

class ForReferenceLibraryControllerTest extends SettingsTestCase
{
    public function testIndex(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $routeName = 'my.settings.compilation.library.for.reference.index';
        $this->signIn($this->mySuperUser($user));

        $reference = Reference::factory()->create();
        $library = Library::factory()->create();
        $library->references()->attach($reference);

        $this->get(route($routeName, ['reference' => $reference]))
            ->assertSuccessful()
            ->assertSee($library->title);
    }
}
