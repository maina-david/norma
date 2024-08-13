<?php

namespace Tests\Feature\Ontology\Collaborate;

use App\Models\Ontology\Category;
use Illuminate\Support\Str;
use Tests\Feature\Traits\HasVisibleFieldAssertions;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use HasVisibleFieldAssertions;

    protected static function visibleFields(): array
    {
        return ['id', 'label', 'display_label', 'category_type'];
    }

    public function testIndex(): void
    {
        $categories = Category::factory(3)->create();

        $routeName = 'collaborate.ontology.categories.index';
        $route = route($routeName, ['parent' => null]);
        $this->validateAuthGuard($route);
        $user = $this->collaboratorSignIn();
        $this->validateCollaborateRole($route);

        $category = Category::factory()->create();
        $categories->each->update(['parent_id' => $category->id]);
        $route = route($routeName, ['parent' => $category->id]);
        $response = $this->get($route)->assertSuccessful();
        $this->assertSeeVisibleFields($categories[0], $response);
        $this->assertSeeVisibleFields($categories[1], $response);
    }

    public function testCrud(): void
    {
        $parent = Category::factory()->create();
        $category = Category::factory()->make(['parent_id' => $parent->id]);

        $routeName = 'collaborate.ontology.categories.store';
        $route = route($routeName, ['parent' => $parent->id]);
        $this->validateAuthGuard($route, 'post');
        $this->collaboratorSignIn();
        $this->validateCollaborateRole($route, 'post');

        $response = $this->followingRedirects()->post($route, $category->toArray())->assertSuccessful();
        $fromDB = Category::where('parent_id', $parent->id)->where('display_label', $category->display_label)->first();

        $this->assertNotNull($fromDB);

        $routeName = 'collaborate.ontology.categories.create';
        $route = route($routeName, ['parent' => $parent->id]);
        $response = $this->get($route)->assertSuccessful();

        $routeName = 'collaborate.ontology.categories.edit';
        $route = route($routeName, ['category' => $fromDB->id, 'parent' => $parent->id]);
        $response = $this->get($route)->assertSuccessful();
        $this->assertSeeVisibleFields($category, $response);

        $routeName = 'collaborate.ontology.categories.update';
        $route = route($routeName, ['parent' => $parent->id, 'category' => $fromDB->id]);
        $newLabel = Str::random();
        $category->label = null;
        $response = $this->withExceptionHandling()->put($route, $category->toArray())->assertSessionHasErrors();
        $category->label = $newLabel;
        $response = $this->followingRedirects()->put($route, $category->toArray())->assertSuccessful();
        $this->assertNotNull($fromDB->refresh());
        $this->assertSame($newLabel, $fromDB->label);

        $routeName = 'collaborate.ontology.categories.destroy';
        $route = route($routeName, ['parent' => $parent->id, 'category' => $fromDB->id]);
        $this->followingRedirects()->delete($route)->assertSuccessful();
        $this->assertNull(Category::find($fromDB->id));
    }

    /**
     * @return void
     */
    public function testParentChildRelationship(): void
    {
        $parent = Category::factory()->create();
        $child = Category::factory()->create(['parent_id' => $parent->id]);
        $grandchild = Category::factory()->create(['parent_id' => $child->id]);
        $greatGrandchild = Category::factory()->create(['parent_id' => $grandchild->id]);

        $this->assertSame(2, $child->refresh()->level);
        $this->assertSame(3, $grandchild->refresh()->level);
        $this->assertSame(4, $greatGrandchild->refresh()->level);

        $grandchild->update(['parent_id' => $parent->id]);

        $this->assertSame(2, $child->refresh()->level);
        $this->assertSame(2, $grandchild->refresh()->level);
        $this->assertSame(3, $greatGrandchild->refresh()->level);
    }
}
