<?php

namespace Tests\Feature\Compilation\Settings;

use App\Models\Compilation\Library;
use App\Models\Corpus\Reference;
use Illuminate\Support\Facades\Session;
use Tests\Feature\Settings\SettingsTestCase;

class SessionLibraryControllerTest extends SettingsTestCase
{
    public function testLoadLibraries(): void
    {
        $routeName = 'my.settings.compilation.libraries.session';
        $route = route($routeName, ['key' => 'compilation']);
        $user = $this->assertForbiddenForNonAdmin($route, 'get');
        $this->signIn($this->mySuperUser($user));
        $library = Library::factory()->create();
        Session::put('libraries_loaded_compilation', [$library->id]);
        $response = $this->withExceptionHandling()->get($route)
            ->assertSuccessful()
            ->assertSee($library->title);
        Session::flush();
    }

    public function testAddLibraryAndClear(): void
    {
        $routeName = 'my.settings.compilation.libraries.session.add';
        $route = route($routeName, ['key' => 'compilation']);
        $user = $this->assertForbiddenForNonAdmin($route, 'post');
        $this->signIn($this->mySuperUser($user));
        $library = Library::factory()->create();

        $response = $this->withExceptionHandling()
            ->followingRedirects()
            ->post($route, ['library_id' => $library->id])
            ->assertSuccessful()
            ->assertSee($library->title)
            ->assertSessionHas('libraries_loaded_compilation', [$library->id]);

        $routeName = 'my.settings.compilation.libraries.session.remove';
        $route = route($routeName, ['key' => 'compilation']);

        $response = $this->withExceptionHandling()
            ->followingRedirects()
            ->delete($route)
            ->assertSuccessful()
            ->assertDontSee($library->title)
            ->assertSessionMissing('libraries_loaded_compilation');
    }

    public function testAddByReferences(): void
    {
        $routeName = 'my.settings.compilation.libraries.session.add.by.references';
        $route = route($routeName, ['key' => 'compilation']);
        $user = $this->assertForbiddenForNonAdmin($route, 'post');
        $this->signIn($this->mySuperUser($user));
        $library = Library::factory()->create();
        $reference = Reference::factory()->create();
        $library->references()->attach($reference);

        $response = $this->withExceptionHandling()
            ->followingRedirects()
            ->post($route, ['references' => [$reference->id]])
            ->assertSuccessful()
            ->assertSessionHas('libraries_loaded_compilation', [$library->id]);
    }
}
