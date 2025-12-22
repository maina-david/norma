<?php

namespace Tests\Feature\Compilation\Settings;

use App\Models\Compilation\Library;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use Illuminate\Support\Str;
use Tests\Feature\Settings\SettingsTestCase;
use Tests\Feature\Traits\TestsVisibleFormLabels;

class LibraryControllerTest extends SettingsTestCase
{
    use TestsVisibleFormLabels;

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @throws Exception
     *
     * @return string
     */
    protected static function resource(): string
    {
        return Library::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @throws Exception
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'my.settings.libraries';
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @throws Exception
     *
     * @return array
     */
    protected static function editFormVisibleLabels(): array
    {
        return ['Title', 'Description', 'Auto Compiled'];
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @throws Exception
     *
     * @return array
     */
    protected static function createFormVisibleLabels(): array
    {
        return ['Title', 'Description', 'Auto Compiled'];
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @throws Exception
     *
     * @return array
     */
    protected static function editFormVisibleFields(): array
    {
        return ['title', 'description'];
    }

    /**
     * @return void
     */
    public function testIndex(): void
    {
        $route = route('my.settings.libraries.index');
        $user = $this->assertForbiddenForNonAdmin($route, 'get');

        $org = Organisation::factory()->create();
        $user->organisations()->attach($org, ['is_admin' => true]);

        $library = Library::factory()->create();
        $norma = Norma::factory()->for($library)->for($org)->create();

        $response = $this->assertCanAccessAfterOrgActivate(route('my.settings.libraries.index', ['activateOrgId' => $org->id]), 'get');
        $response->assertSee($library->title);
        $response->assertSee('Libraries');

        $response = $this->get(route('my.settings.libraries.index', ['search' => Str::random(10)]));
        $response->assertDontSee($library->title);
        $response = $this->get(route('my.settings.libraries.index', ['search' => $library->title]));
        $response->assertSee($library->title);
        $response = $this->get(route('my.settings.libraries.index', ['search' => substr($library->title, 0, 4)]));
        $response->assertSee($library->title);
    }

    public function testIndexAllOrgs(): void
    {
        $user = $this->activateAllOrg();

        $library = Library::factory()->create();
        $library2 = Library::factory()->create();

        // when viewing in all organisations mode, you should see all libraries
        $response = $this->get(route('my.settings.libraries.index'));
        $response->assertSuccessful();
        $response->assertSee('Libraries');

        $response->assertSee($library->title);
        $response->assertSee($library2->title);
        $response->assertSeeSelector('//table//tr//*[contains(text(),"' . $library->title . '")]');
        $response->assertSeeSelector('//table//tr//*[contains(text(),"' . $library2->title . '")]');
    }

    public function testIndexJson(): void
    {
        $user = $this->activateAllOrg();

        $library = Library::factory()->create();
        // when viewing in all organisations mode, you should see all teams the user is part of
        $response = $this->get(route('my.settings.libraries.index'), ['Accept' => 'application/json']);
        $response->assertSuccessful();
        $response->assertJsonFragment([['id' => $library->id, 'title' => $library->title, 'details' => $library->description]]);
    }

    /**
     * @return void
     */
    public function testShow(): void
    {
        $user = $this->signIn();
        $org = Organisation::factory()->create();
        $library = Library::factory()->create();
        $norma = Norma::factory()->for($library)->for($org)->create();

        $route = route('my.settings.libraries.show', ['library' => $library->id]);
        $user = $this->assertForbiddenForNonAdmin($route, 'get', $user);
        $user->organisations()->attach($org, ['is_admin' => true]);

        $routeActivate = route('my.settings.libraries.show', ['library' => $library->id, 'activateOrgId' => $org->id]);
        $response = $this->assertCanAccessAfterOrgActivate($routeActivate, 'get');

        $response->assertSee($library->title);
        $response->assertSeeSelector('//header//div[contains(text(),"' . $library->title . '")]');
    }

    /**
     * @return void
     */
    public function testEdit(): void
    {
        $this->runEditTest(allOrgMode: true);

        // $response = $this->get($route)->assertForbidden();
    }

    /**
     * @return void
     */
    public function testUpdate(): void
    {
        $this->runUpdateTest(allOrgMode: true);
    }

    /**
     * @return void
     */
    public function testCreate(): void
    {
        $this->runCreateTest(allOrgMode: true);
    }

    /**
     * @return void
     */
    public function testStore(): void
    {
        $this->runStoreTest([], false, true);
    }

    /**
     * @return void
     */
    public function testDestroy(): void
    {
        $this->runDestroyTest(allOrgMode: true);
    }
}
