<?php

namespace Tests\Feature\Ontology\Collaborate;

use App\Models\Ontology\Category;
use App\Models\Ontology\CategoryDescription;
use Tests\TestCase;

class CategoryDescriptionControllerTest extends TestCase
{
    public function testCrud(): void
    {
        $item = Category::factory()->create();
        $routeName = 'collaborate.categories.descriptions.create';
        $route = route($routeName, ['category' => $item]);
        $this->validateAuthGuard($route);
        $this->collaboratorSignIn();
        $this->validateCollaborateRole($route);

        $this->get($route)
            ->assertSuccessful()
            ->assertSee('Country')
            ->assertSee('Explanation');

        $description = CategoryDescription::factory()->for($item)->make();

        $routeName = 'collaborate.categories.descriptions.store';

        $this->followingRedirects()
            ->post(route($routeName, ['category' => $item]), $description->toArray())
            ->assertSuccessful();

        $description = $item->descriptions->first();

        $routeName = 'collaborate.categories.descriptions.edit';
        $this->get(route($routeName, ['category' => $item, 'description' => $description->id]))
            ->assertSuccessful()
            ->assertSee('Country')
            ->assertSee('Explanation');

        $routeName = 'collaborate.categories.descriptions.update';
        $newText = 'New description';
        $description->description = $newText;
        $this->followingRedirects()
            ->put(route($routeName, ['category' => $item, 'description' => $description->id]), $description->toArray())
            ->assertSuccessful();
        $this->assertSame($newText, $description->refresh()->description);

        $routeName = 'collaborate.categories.descriptions.destroy';
        $response = $this->followingRedirects()
            ->delete(route($routeName, ['category' => $item, 'description' => $description->id]))
            ->assertSuccessful();
    }
}
