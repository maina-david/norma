<?php

namespace Tests\Feature\Compilation\Settings;

use App\Enums\Corpus\ReferenceType;
use App\Models\Compilation\Library;
use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use App\Models\Geonames\Location;
use App\Models\Ontology\LegalDomain;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Settings\SettingsTestCase;

class GenericCompilationControllerTest extends SettingsTestCase
{
    public function testForbiddenForNonAdmin(): void
    {
        $library = Library::factory()->create();
        $route = route('my.settings.compilation.generic.compile.show', ['library' => $library->id]);
        $user = $this->assertForbiddenForNonAdmin($route, 'get');
        $route = route('my.settings.compilation.generic.decompile.show', ['library' => $library->id]);
        $this->assertForbiddenForNonAdmin($route, 'get', $user);
        $route = route('my.settings.compilation.generic.compile', ['library' => $library->id]);
        $this->assertForbiddenForNonAdmin($route, 'post', $user);
        $route = route('my.settings.compilation.generic.decompile', ['library' => $library->id]);
        $this->assertForbiddenForNonAdmin($route, 'post', $user);
    }

    public function testShowCompile(): void
    {
        $library = Library::factory()->create();
        $route = route('my.settings.compilation.generic.compile.show', ['library' => $library->id]);
        $response = $this->runShowTest($route);
        $response->assertSeeSelector('//header//*[text()[contains(.,"Compile")]]');
    }

    public function testShowDecompile(): void
    {
        $library = Library::factory()->create();
        $route = route('my.settings.compilation.generic.decompile.show', ['library' => $library->id]);
        $response = $this->runShowTest($route);
        $response->assertSeeSelector('//header//*[text()[contains(.,"Decompile")]]');
    }

    /**
     * @param string $route
     *
     * @return TestResponse
     */
    private function runShowTest(string $route): TestResponse
    {
        $this->activateAllOrg();
        $response = $this->get($route)->assertSuccessful();
        $response->assertSeeSelector('//label[text()[contains(.,"Jurisdiction")]]');
        $response->assertSeeSelector('//label[text()[contains(.,"legal domains")]]');
        $response->assertSeeSelector('//label[text()[contains(.,"Include legislation of all child locations")]]');

        return $response;
    }

    public function testCompileDecompile(): void
    {
        $this->activateAllOrg();
        $library = Library::factory()->create();
        $location = Location::factory()->create();
        $route = route('my.settings.compilation.generic.compile', ['library' => $library->id]);
        $decompileRoute = route('my.settings.compilation.generic.decompile', ['library' => $library->id]);
        // location is required
        $this->post($route)->assertInvalid();

        $work = Work::factory()->create();
        $reference = Reference::factory()->for($work)->create();
        $reference2 = Reference::factory()->for($work)->create(['type' => ReferenceType::constitutive()->value, 'referenceable_type' => 'legal_statements']);
        $reference3 = Reference::factory()->for($work)->create();
        $reference3->refRequirement?->delete();
        $reference->locations()->attach($location);
        $reference2->locations()->attach($location);
        $reference3->locations()->attach($location);

        $this->followingRedirects()->post($route, [
            'location_id' => $location->id,
        ])->assertSuccessful();

        $this->assertTrue($library->references->contains($reference));
        // $reference2 is of type legal_statements, so shouldn't be compiled
        $this->assertFalse($library->references->contains($reference2));
        // $reference3 doesn't have obligations, so shouldn't be compiled
        $this->assertFalse($library->references->contains($reference3));

        // and then decompile
        $this->followingRedirects()->post($decompileRoute, [
            'location_id' => $location->id,
        ])->assertSuccessful();

        $this->assertFalse($library->fresh()->references->contains($reference));

        $legalDomain = LegalDomain::factory()->create();
        $this->followingRedirects()->post($route, [
            'location_id' => $location->id,
            'legal_domains' => [$legalDomain->id],
        ])->assertSuccessful();
        $this->assertFalse($library->fresh()->references->contains($reference));

        $reference->legalDomains()->attach($legalDomain);
        $this->followingRedirects()->post($route, [
            'location_id' => $location->id,
            'legal_domains' => [$legalDomain->id],
        ])->assertSuccessful();
        $this->assertTrue($library->fresh()->references->contains($reference));

        $location2 = Location::factory()->create();
        $location->descendants()->attach($location2, ['depth' => 1]);
        $reference4 = Reference::factory()->for($work)->create();
        $reference4->locations()->attach($location2);
        $this->followingRedirects()->post($route, [
            'location_id' => $location->id,
        ])->assertSuccessful();
        $this->assertFalse($library->fresh()->references->contains($reference4));

        $this->followingRedirects()->post($route, [
            'location_id' => $location->id,
            'include_children' => true,
        ])->assertSuccessful();

        $this->assertTrue($library->fresh()->references->contains($reference4));
    }
}
