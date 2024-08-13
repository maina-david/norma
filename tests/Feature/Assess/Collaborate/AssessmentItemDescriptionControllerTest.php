<?php

namespace Tests\Feature\Assess\Collaborate;

use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemDescription;
use Tests\TestCase;

class AssessmentItemDescriptionControllerTest extends TestCase
{
    public function testIndex(): void
    {
        $item = AssessmentItem::factory()->create();
        $descriptions = AssessmentItemDescription::factory(3)->for($item)->create();
        $routeName = 'collaborate.assessment-items.descriptions.index';
        $route = route($routeName, ['item' => $item]);
        $this->validateAuthGuard($route);
        $this->collaboratorSignIn();
        $this->validateCollaborateRole($route);

        $response = $this->get($route)
            ->assertRedirect(route('collaborate.assessment-items.show', [
                'assessment_item' => $item->id, 'tab' => 'explanations',
            ]));

        $response = $this->followRedirects($response);

        $response->assertSee($descriptions[0]->description);
        $response->assertSee($descriptions[0]->location->title);
    }

    public function testCrud(): void
    {
        $item = AssessmentItem::factory()->create();
        $routeName = 'collaborate.assessment-items.descriptions.create';
        $route = route($routeName, ['item' => $item]);
        $this->validateAuthGuard($route);
        $this->collaboratorSignIn();
        $this->validateCollaborateRole($route);

        $this->get($route)
            ->assertSuccessful()
            ->assertSee('Country')
            ->assertSee('Explanation');

        $description = AssessmentItemDescription::factory()->for($item)->make();

        $routeName = 'collaborate.assessment-items.descriptions.store';

        $this->followingRedirects()
            ->post(route($routeName, ['item' => $item]), $description->toArray())
            ->assertSuccessful();

        $description = $item->descriptions->first();

        $routeName = 'collaborate.assessment-items.descriptions.edit';
        $this->get(route($routeName, ['item' => $item, 'description' => $description->id]))
            ->assertSuccessful()
            ->assertSee('Country')
            ->assertSee('Explanation');

        $routeName = 'collaborate.assessment-items.descriptions.update';
        $newText = 'New description';
        $description->description = $newText;
        $this->followingRedirects()
            ->put(route($routeName, ['item' => $item, 'description' => $description->id]), $description->toArray())
            ->assertSuccessful();
        $this->assertSame($newText, $description->refresh()->description);

        $routeName = 'collaborate.assessment-items.descriptions.destroy';
        $response = $this->followingRedirects()
            ->delete(route($routeName, ['item' => $item, 'description' => $description->id]))
            ->assertSuccessful();
    }
}
