<?php

namespace Tests\Feature\Customer\My\Settings;

use App\Models\Compilation\RequirementsCollection;
use App\Models\Customer\Norma;
use Tests\Feature\Settings\SettingsTestCase;
use Tests\Feature\Traits\HasSettingsPivotCrudTests;

class NormaRequirementsCollectionControllerTest extends SettingsTestCase
{
    use HasSettingsPivotCrudTests;

    protected static function resourceRoute(): string
    {
        return 'my.settings.normas.compilation.requirements-collections';
    }

    protected static function resource(): string
    {
        return RequirementsCollection::class;
    }

    protected static function forResource(): ?string
    {
        return Norma::class;
    }

    protected static function pivotRelationName(): string
    {
        return 'requirementsCollections';
    }

    /**
     * @return void
     */
    public function testInheritance(): void
    {
        $forResource = static::forResource();
        $resource = static::resource();
        $parent = $forResource::factory()->create();
        $child = $resource::factory()->create();
        $route = route(static::resourceRoute() . '.store', [$parent->id]);
        $user = $this->assertForbiddenForNonAdmin($route, 'post');
        $this->signIn($this->mySuperUser());
        $this->withAllOrgMode();

        // when viewing in all organisations mode, you should see all teams the norma is part of
        $response = $this->followingRedirects()->post($route, [
            'ids' => [$child->id],
            'include_ancestors' => true,
            'include_descendants' => true,
        ])->assertSuccessful();
        $response->assertSee($parent->title);
        $response->assertSee($child->title);
    }
}
