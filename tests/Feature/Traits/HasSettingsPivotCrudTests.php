<?php

namespace Tests\Feature\Traits;

trait HasSettingsPivotCrudTests
{
    /**
     * @return void
     */
    public function testIndex(): void
    {
        $forResource = static::forResource();
        $resource = static::resource();
        $relationName = static::pivotRelationName();
        $parent = $forResource::factory()->create();
        $child = $resource::factory()->create();
        $parent->{$relationName}()->attach($child);
        $route = route(static::resourceRoute() . '.index', [$parent->id]);
        $user = $this->assertForbiddenForNonAdmin($route, 'get');

        $this->signIn($this->mySuperUser());
        $this->withAllOrgMode();

        // when viewing in all organisations mode, you should see all teams the libryo is part of
        $response = $this->get($route)->assertSuccessful();
        $response->assertSee($parent->title);
        $response->assertSee($child->title);
    }

    /**
     * @return void
     */
    public function testCreate(): void
    {
        $forResource = static::forResource();
        $parent = $forResource::factory()->create();
        $route = route(static::resourceRoute() . '.create', [$parent->id]);
        $user = $this->assertForbiddenForNonAdmin($route, 'get');
        $this->signIn($this->mySuperUser());
        $this->withAllOrgMode();
        // when viewing in all organisations mode, you should see all teams the libryo is part of
        $response = $this->get($route)->assertSuccessful();
        $response->assertSee($parent->title);
    }

    /**
     * @return void
     */
    public function testStore(): void
    {
        $forResource = static::forResource();
        $resource = static::resource();
        $parent = $forResource::factory()->create();
        $child = $resource::factory()->create();
        $route = route(static::resourceRoute() . '.store', [$parent->id]);
        $user = $this->assertForbiddenForNonAdmin($route, 'post');
        $this->signIn($this->mySuperUser());
        $this->withAllOrgMode();

        // when viewing in all organisations mode, you should see all teams the libryo is part of
        $response = $this->followingRedirects()->post($route, ['ids' => [$child->id]])->assertSuccessful();
        $response->assertSee($parent->title);
        $response->assertSee($child->title);
    }

    /**
     * @return void
     */
    public function testDestroy(): void
    {
        $forResource = static::forResource();
        $resource = static::resource();
        $relationName = static::pivotRelationName();

        $parent = $forResource::factory()->create();
        $child = $resource::factory()->create();
        $parent->{$relationName}()->attach($child);
        $route = route(static::resourceRoute() . '.destroy', [$parent->id, $child->id]);
        $user = $this->assertForbiddenForNonAdmin($route, 'delete');

        $this->signIn($this->mySuperUser());
        $this->withAllOrgMode();

        // when viewing in all organisations mode, you should see all teams the libryo is part of
        $response = $this->followingRedirects()->delete($route)->assertSuccessful();
        $response->assertSee($parent->title);
        $response->assertDontSee($child->title);
    }
}
