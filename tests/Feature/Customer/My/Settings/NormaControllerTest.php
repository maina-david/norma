<?php

namespace Tests\Feature\Customer\My\Settings;

use App\Models\Compilation\ContextQuestion;
use App\Models\Corpus\Reference;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Geonames\Location;
use App\Models\Ontology\Category;
use App\Models\Ontology\CategoryType;
use App\Models\Partners\Partner;
use Exception;
use Illuminate\Support\Str;
use Tests\Feature\Settings\SettingsTestCase;

class NormaControllerTest extends SettingsTestCase
{
    /**
     * Get the class to be used for the CRUD operations.
     *
     * @throws Exception
     *
     * @return string
     */
    protected static function resource(): string
    {
        return Norma::class;
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
        return 'my.settings.normas';
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
        return ['Title', 'Address', 'Latitude', 'Longitude'];
        // return ['Title', 'Address', 'Latitude', 'Longitude', 'Library', 'Organisation', 'Jurisdiction', 'Compilation in Progress', 'Force new compilation in next cycle'];
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
        return ['title', 'address', 'geo_lat', 'geo_lng'];
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
        return ['Title', 'Address', 'Latitude', 'Longitude'];
    }

    /**
     * @return void
     */
    public function testIndex(): void
    {
        $routeName = 'my.settings.normas.index';
        $route = route($routeName);
        $user = $this->assertForbiddenForNonAdmin($route, 'get');

        $partner = Partner::factory()->create();
        $org = Organisation::factory()->create(['partner_id' => $partner->id]);
        $user->organisations()->attach($org, ['is_admin' => true]);
        $norma = Norma::factory()->for($org)->create(['organisation_id' => $org->id]);

        $norma2 = Norma::factory()->for($org)->create(['organisation_id' => $org->id, 'integration_id' => '123']);

        $response = $this->assertCanAccessAfterOrgActivate(route($routeName, ['activateOrgId' => $org->id]), 'get');
        $response->assertSee($norma->title);
        $response->assertSee('Norma Streams');

        $response = $this->get(route($routeName, ['search' => Str::random(10)]));
        $response->assertDontSee($norma->title);
        $response = $this->get(route($routeName, ['search' => $norma->title]));
        $response->assertSee($norma->title);
        $response = $this->get(route($routeName, ['search' => substr($norma->title, 0, 4)]));
        $response->assertSee($norma->title);

        $reference = Reference::factory()->create();
        $norma->references()->attach($reference);
        $response = $this->get(route($routeName, ['citations' => [$reference->id]]));
        $response->assertSee($norma->title);
        $response->assertDontSee($norma2->title);
    }

    public function testIndexAllOrgs(): void
    {
        $user = $this->activateAllOrg();

        $partner = Partner::factory()->create();
        $org = Organisation::factory()->create(['partner_id' => $partner->id]);
        $norma = Norma::factory()->for($org)->create(['integration_id' => '123']);
        $norma2 = Norma::factory()->create(['deactivated' => true]);
        $user->normas()->attach($norma->id);

        // when viewing in all organisations mode, you should see all normas
        $response = $this->get(route('my.settings.normas.index'));
        $response->assertSuccessful();
        $response->assertSee('Norma Streams');

        $response->assertSee($norma->title);
        $response->assertSee($norma2->title);
        $response->assertSeeSelector('//table//tr//*[contains(text(),"' . $norma->title . '")]');
        $response->assertSeeSelector('//table//tr//*[contains(text(),"' . $norma2->title . '")]');
        $response->assertSeeSelector('//table//tr//*[contains(text(),"Yes")]');
        $response->assertSeeSelector('//table//tr//*[contains(text(),"No")]');
    }

    public function testIndexJson(): void
    {
        $user = $this->activateAllOrg();

        $norma = Norma::factory()->create();
        // when viewing in all organisations mode, you should see all teams the user is part of
        $response = $this->get(route('my.settings.normas.index'), ['Accept' => 'application/json']);
        $response->assertSuccessful();
        $response->assertJsonFragment([['id' => $norma->id, 'title' => $norma->title]]);
    }

    /**
     * @return void
     */
    public function testDeactivateActivateAction(): void
    {
        $org = Organisation::factory()->create();
        $route = route('my.settings.normas.actions.organisation', ['organisation' => $org->id]);
        $user = $this->assertForbiddenForNonAdmin($route, 'post');

        $norma1 = Norma::factory()->for($org)->create();
        $norma2 = Norma::factory()->for($org)->create();
        $norma3 = Norma::factory()->create();

        $user->organisations()->attach($org, ['is_admin' => true]);

        // test posting with 'action', and with one item
        $response = $this->withActivatedOrg($org)->post($route, [
            'action' => 'deactivate',
            'actions-checkbox-' . $norma1->id => 'on',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('flash.message', 'Success!');
        $this->assertTrue(Norma::find($norma1->id)->deactivated);

        $superUser = $this->mySuperUser();
        $this->signIn($superUser);

        $routeAll = route('my.settings.normas.actions.all');
        $response = $this->post($routeAll, [
            'action' => 'deactivate',
            'actions-checkbox-' . $norma3->id => 'on',
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('flash.message', 'Success!');
        $this->assertTrue(Norma::find($norma3->id)->deactivated);

        $response = $this->post($routeAll, [
            'action' => 'activate',
            'actions-checkbox-' . $norma3->id => 'on',
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('flash.message', 'Success!');
        $this->assertFalse(Norma::find($norma3->id)->deactivated);
    }

    /**
     * @return void
     */
    public function testShow(): void
    {
        $user = $this->signIn();
        $org = Organisation::factory()->create();
        $norma = Norma::factory()->for($org)->create();
        $route = route('my.settings.normas.show', ['norma' => $norma->id]);
        $user = $this->assertForbiddenForNonAdmin($route, 'get', $user);
        $user->organisations()->attach($org, ['is_admin' => true]);

        $routeActivate = route('my.settings.normas.show', ['norma' => $norma->id, 'activateOrgId' => $org->id]);
        $response = $this->assertCanAccessAfterOrgActivate($routeActivate, 'get');

        $response->assertSee($norma->title);
        $response->assertSee($norma->address);
        $response->assertSeeSelector('//header//div//span[contains(text(),"' . $norma->title . '")]');
    }

    /**
     * @return void
     */
    public function testEdit(): void
    {
        CategoryType::factory()->create(['id' => \App\Enums\Ontology\CategoryType::ECONOMIC->value]);
        $parent = Category::factory()->create(['category_type_id' => \App\Enums\Ontology\CategoryType::ECONOMIC->value, 'level' => 1]);
        $parent = Category::factory()->create(['category_type_id' => \App\Enums\Ontology\CategoryType::ECONOMIC->value, 'parent_id' => $parent->id, 'level' => 2]);
        $parent->descriptions()->create(['description' => $this->faker->paragraph]);
        $parent = Category::factory()->create(['category_type_id' => \App\Enums\Ontology\CategoryType::ECONOMIC->value, 'parent_id' => $parent->id, 'level' => 3]);
        $parent->descriptions()->create(['description' => $this->faker->paragraph]);
        Category::factory()->create(['category_type_id' => \App\Enums\Ontology\CategoryType::ECONOMIC->value, 'parent_id' => $parent->id, 'level' => 4]);

        [$user, $org, $resource, $route, $response] = $this->runEditTest(true);
        $this->editAndCreateDontSeeAssertions($response);

        $this->activateAllOrg();
        $response = $this->get($route)->assertSuccessful();
        $this->editAndCreateSeeAssertions($response);
    }

    private function editAndCreateDontSeeAssertions($response): void
    {
        $response->assertDontSeeSelector('//label[text()[contains(.,"Organisation")]]');
        $response->assertDontSeeSelector('//label[text()[contains(.,"Library")]]');
        $response->assertDontSeeSelector('//label[text()[contains(.,"Jurisdiction")]]');
        //        $response->assertDontSeeSelector('//label[text()[contains(.,"Compilation in Progress")]]');
        $response->assertDontSeeSelector('//label[text()[contains(.,"Force new compilation in next cycle")]]');
    }

    private function editAndCreateSeeAssertions($response): void
    {
        $response->assertSeeSelector('//label[text()[contains(.,"Organisation")]]');
        $response->assertSeeSelector('//label[text()[contains(.,"Library")]]');
        $response->assertSeeSelector('//label[text()[contains(.,"Jurisdiction")]]');
        //        $response->assertSeeSelector('//label[text()[contains(.,"Compilation in Progress")]]');
        $response->assertSeeSelector('//label[text()[contains(.,"Force new compilation in next cycle")]]');
    }

    /**
     * @return void
     */
    public function testUpdate(): void
    {
        [$user, $org, $resource, $route, $response, $editRoute] = $this->runUpdateTest(true);
        $jurisdiction = Location::factory()->create();

        $this->activateAllOrg();
        // test can't update without organisation_id
        $updatedTitle = 'Updated title 2';
        $response = $this->put($route, ['title' => $updatedTitle, 'geo_lat' => 'test', 'organisation_id' => $org->id]);
        $response->assertInvalid();
        $response = $this->put($route, ['title' => $updatedTitle, 'geo_lat' => 91.828282, 'organisation_id' => $org->id]);
        $response->assertInvalid();
        $response = $this->put($route, ['title' => $updatedTitle, 'geo_lng' => 181.8765, 'organisation_id' => $org->id]);
        $response->assertInvalid();
        $response = $this->put($route, ['title' => $updatedTitle, 'geo_lat' => -91.8, 'organisation_id' => $org->id]);
        $response->assertInvalid();
        $response = $this->put($route, ['title' => $updatedTitle, 'geo_lng' => -181.8, 'organisation_id' => $org->id]);
        $response->assertInvalid();
        // too long
        $response = $this->put($route, ['title' => Str::random(256), 'organisation_id' => $org->id]);
        $response->assertInvalid();
        $response = $this->put($route, ['title' => Str::random(255), 'organisation_id' => $org->id, 'location_id' => $jurisdiction->id]);
        $response->assertValid();
        // title required
        $response = $this->put($route, ['address' => 'test', 'organisation_id' => $org->id]);
        $response->assertInValid();

        $updatedTitle = 'Updated title 3';
        $response = $this->put($route, ['title' => $updatedTitle, 'organisation_id' => $org->id, 'location_id' => $jurisdiction->id]);
        $response->assertValid();
        // test updating with a title that already exists in org
        $norma = Norma::factory()->for($org)->create();
        $updatedTitle = $norma->title;
        $response = $this->put($route, ['title' => $updatedTitle, 'organisation_id' => $org->id]);
        $response->assertInValid();
        $updatedTitle = 'Updated title 4';
        $response = $this->put($route, ['title' => $updatedTitle, 'organisation_id' => $org->id, 'location_id' => $jurisdiction->id, 'geo_lat' => -50.345, 'geo_lng' => -100.4545]);
        $response->assertValid();
        $updatedTitle = 'Updated title 5';
        $response = $this->put($route, ['title' => $updatedTitle, 'organisation_id' => $org->id, 'location_id' => $jurisdiction->id, 'geo_lat' => 80.345, 'geo_lng' => 90.4545]);
        $response->assertValid();
    }

    /**
     * @return void
     */
    public function testCreate(): void
    {
        [$user, $org, $resource, $route, $response] = $this->runCreateTest();
        $this->editAndCreateDontSeeAssertions($response);

        $this->activateAllOrg();
        $response = $this->get($route)->assertSuccessful();
        $this->editAndCreateSeeAssertions($response);
    }

    /**
     * @return void
     */
    public function testStore(): void
    {
        [$user, $org, $resource, $route, $response] = $this->runStoreTest(['organisation_id', 'location_id', 'library_id'], true);
    }

    /**
     * @return void
     */
    public function testModules(): void
    {
        $norma = Norma::factory()->create();
        $route = route('my.settings.normas.modules.index', ['norma' => $norma->id]);
        $this->assertForbiddenForNonAdmin($route, 'get');

        $this->activateAllOrg();
        $response = $this->get($route)->assertSuccessful();

        $response->assertSee('Assess');
        $response->assertSee('Drives');
        $response->assertSee('Update Emails');
        $response->assertSee('Keyword Search');
        $response->assertSee('Search Requirements and Drives');
    }

    /**
     * @return void
     */
    public function testUpdatesModules(): void
    {
        $norma = Norma::factory()->create();
        $route = route('my.settings.normas.modules.update', ['norma' => $norma->id]);
        $this->assertForbiddenForNonAdmin($route, 'post');

        $this->activateAllOrg();

        $this->assertFalse($norma->settings['modules']['keyword_search']);
        $this->assertTrue($norma->settings['modules']['drives']);
        $this->followingRedirects()->post($route, ['keyword_search' => true, 'drives' => false])->assertSuccessful();
        $norma = $norma->fresh();
        $this->assertTrue($norma->settings['modules']['keyword_search']);
        $this->assertFalse($norma->settings['modules']['drives']);
    }

    /**
     * @return void
     */
    public function testCompilationSettings(): void
    {
        $norma = Norma::factory()->create();
        $route = route('my.settings.normas.compilation-settings.index', ['norma' => $norma->id]);
        $this->assertForbiddenForNonAdmin($route, 'get');

        $this->activateAllOrg();
        $response = $this->get($route)->assertSuccessful();

        $response->assertSee('Use Collections');
        $response->assertSee('Use Legal Domains');
        $response->assertSee('Use Context Questions');
        $response->assertSee('Use Topics');
        $response->assertSee('Include requirements without legal domains attached');
        $response->assertSee('Include requirements without context questions attached');
        $response->assertSee('Include requirements without topics attached');
    }

    /**
     * @return void
     */
    public function testCompilationSettingsUpdate(): void
    {
        $norma = Norma::factory()->create();
        $route = route('my.settings.normas.compilation-settings.update', ['norma' => $norma->id]);
        $this->assertForbiddenForNonAdmin($route, 'post');

        $this->activateAllOrg();

        $this->assertTrue($norma->compilationSetting->use_collections);

        // update with opposite of defaults
        $this->followingRedirects()->post($route, [
            'use_collections' => false,
            'use_legal_domains' => false,
            'include_no_legal_domains' => true,
            'use_context_questions' => false,
            'include_no_context_questions' => false,
            'use_topics' => true,
            'include_no_topics' => true,
        ])->assertSuccessful();
        $norma->compilationSetting->refresh();
        $this->assertFalse($norma->compilationSetting->use_collections);
        $this->assertFalse($norma->compilationSetting->use_legal_domains);
        $this->assertTrue($norma->compilationSetting->include_no_legal_domains);
        $this->assertFalse($norma->compilationSetting->use_context_questions);
        $this->assertFalse($norma->compilationSetting->include_no_context_questions);
        $this->assertTrue($norma->compilationSetting->use_topics);
        $this->assertTrue($norma->compilationSetting->include_no_topics);
    }

    /**
     * @return void
     */
    public function testCloning(): void
    {
        /** @var Norma $norma */
        $norma = Norma::factory()->create();
        $context = ContextQuestion::factory()->create();
        $norma->contextQuestions()->attach($context->id);

        $route = route('my.settings.norma.clone', ['norma' => $norma->id]);

        $this->assertForbiddenForNonAdmin($route, 'post');
        $this->activateAllOrg();

        $this->followingRedirects()
            ->post($route)
            ->assertSessionHasNoErrors()
            ->assertSee('- Copy');
    }

    public function testNormaStreamCreationValidation(): void
    {
        $this->signIn($this->mySuperUser());

        $normaInput = Norma::factory()->raw(['location_id' => null]);

        $response = $this->post(route('my.settings.normas.store', $normaInput));

        $response->assertSessionHasErrors(['location_id']);
        $this->assertDatabaseCount('places', 0);
    }
}
