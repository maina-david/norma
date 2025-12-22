<?php

namespace Tests\Feature\Compilation\Settings;

use App\Models\Compilation\Library;
use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use App\Models\Requirements\Summary;
use Illuminate\Support\Facades\Session;
use Tests\Feature\Settings\SettingsTestCase;

class ManualCompilationControllerTest extends SettingsTestCase
{
    public function testCompilation(): void
    {
        $routeName = 'my.settings.compilation.manual-compilation';
        $route = route($routeName);
        $user = $this->assertForbiddenForNonAdmin($route, 'get');
        $this->signIn($this->mySuperUser($user));
        $this->withExceptionHandling()->get(route($routeName))->assertSuccessful();
    }

    public function testAddWorkAndClear(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $routeName = 'my.settings.compilation.works.for.manual.compilation.add';
        $this->signIn($this->mySuperUser($user));

        $work = Work::factory()->create();
        $workChild = Work::factory()->create();
        $work->children()->attach($workChild);

        $this->withExceptionHandling()->followingRedirects()->post(route($routeName), [
            'work_id' => $work->id,
            'include_children' => 'true',
        ])
            ->assertSuccessful()
            ->assertSee($work->title)
            ->assertSee($workChild->title)
            ->assertSessionHas('manual_compilation_works');

        $this->assertTrue(in_array($work->id, Session::get('manual_compilation_works')));
        $this->assertTrue(in_array($workChild->id, Session::get('manual_compilation_works')));

        // now clear the works
        $routeName = 'my.settings.compilation.works.for.manual.compilation.remove';
        $this->withExceptionHandling()
            ->followingRedirects()
            ->delete(route($routeName))
            ->assertSuccessful()
            ->assertSessionMissing('manual_compilation_works');
    }

    public function testCompileDecompileReference(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $routeName = 'my.settings.compilation.manual.reference.add';
        $this->signIn($this->mySuperUser($user));

        $reference = Reference::factory()->create();
        $library = Library::factory()->create();
        Session::put('libraries_loaded_compilation', [$library->id]);

        $this->get(route($routeName, ['reference' => $reference->id]))->assertSuccessful();
        $this->assertTrue($library->references->contains($reference));
        // and run it again...
        $this->get(route($routeName, ['reference' => $reference->id]))->assertSuccessful();
        $this->assertTrue($library->references->contains($reference));

        $this->get(route('my.settings.compilation.manual.reference.remove', ['reference' => $reference->id]))->assertSuccessful();
        $this->assertFalse($library->refresh()->references->contains($reference));
    }

    public function testCompileDecompileWork(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $routeName = 'my.settings.compilation.manual.work.add';
        $this->signIn($this->mySuperUser($user));

        $work = Work::factory()->has(Reference::factory())->create();
        $reference = $work->references->first();
        Summary::factory()->create(['reference_id' => $reference->id]);

        $library = Library::factory()->create();
        Session::put('libraries_loaded_compilation', [$library->id]);

        $this->get(route($routeName, ['work' => $work->id]))->assertSuccessful();
        $this->assertTrue($library->references->contains($reference));
        // and run it again...
        $this->get(route($routeName, ['work' => $work->id]))->assertSuccessful();
        $this->assertTrue($library->references->contains($reference));

        $this->get(route('my.settings.compilation.manual.work.remove', ['work' => $work->id]))->assertSuccessful();
        $this->assertFalse($library->refresh()->references->contains($reference));
    }

    public function testCompileDecompileAllWorks(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $routeName = 'my.settings.compilation.manual.all.works.add';
        $this->signIn($this->mySuperUser($user));

        $work = Work::factory()->has(Reference::factory())->create();
        $reference = $work->references->first();
        Summary::factory()->create(['reference_id' => $reference->id]);
        $library = Library::factory()->create();
        Session::put('libraries_loaded_compilation', [$library->id]);
        Session::put('manual_compilation_works', [$work->id]);

        $this->get(route($routeName))->assertSuccessful();
        $this->assertTrue($library->references->contains($reference));

        $this->get(route('my.settings.compilation.manual.all.works.remove'))->assertSuccessful();
        $this->assertFalse($library->refresh()->references->contains($reference));
    }
}
