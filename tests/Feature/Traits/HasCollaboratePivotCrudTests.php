<?php

namespace Tests\Feature\Traits;

trait HasCollaboratePivotCrudTests
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
        $this->validateAuthGuard($route);
        $this->collaboratorSignIn();
        $this->validateCollaborateRole($route);

        $response = $this->get($route)->assertSuccessful();

        $assertSeeFieldIndex = method_exists($this, 'titleField') ? static::titleField() : 'title';
        $response->assertSee($parent->{$assertSeeFieldIndex});
        $response->assertSee($child->{$assertSeeFieldIndex});
    }

    /**
     * @return void
     */
    public function testCreate(): void
    {
        $forResource = static::forResource();
        $parent = $forResource::factory()->create();
        $route = route(static::resourceRoute() . '.create', [$parent->id]);
        $this->validateAuthGuard($route);
        $this->collaboratorSignIn();
        $this->validateCollaborateRole($route);

        $this->get($route)->assertSuccessful();
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
        $this->validateAuthGuard($route, 'post');
        $this->collaboratorSignIn();
        $this->validateCollaborateRole($route, 'post');

        $response = $this->followingRedirects()->post($route, ['ids' => [$child->id]])->assertSuccessful();

        $assertSeeFieldIndex = method_exists($this, 'titleField') ? static::titleField() : 'title';
        $response->assertSee($parent->{$assertSeeFieldIndex});
        $response->assertSee($child->{$assertSeeFieldIndex});
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
        $this->validateAuthGuard($route, 'delete');
        $this->collaboratorSignIn();
        $this->validateCollaborateRole($route, 'delete');

        // when viewing in all organisations mode, you should see all teams the libryo is part of
        $response = $this->followingRedirects()->delete($route)->assertSuccessful();

        $assertSeeFieldIndex = method_exists($this, 'titleField') ? static::titleField() : 'title';
        $response->assertSee($parent->{$assertSeeFieldIndex});
        $response->assertDontSee($child->{$assertSeeFieldIndex});
    }
}
