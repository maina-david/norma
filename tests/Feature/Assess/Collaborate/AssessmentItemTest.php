<?php

namespace Tests\Feature\Assess\Collaborate;

use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemDescription;
use App\Models\Geonames\Location;
use Tests\Feature\Abstracts\CrudTestCase;

class AssessmentItemTest extends CrudTestCase
{
    /** @var string */
    protected string $sortBy = 'component_1';

    protected bool $collaborate = true;

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return AssessmentItem::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.assessment-items';
    }

    /**
     * The list of database columns that should be visible on the pages.
     *
     * @return string[]
     */
    protected static function visibleFields(): array
    {
        return ['component_2'];
    }

    /**
     * The list of database columns that should be visible on the pages with forms.
     *
     * @return string[]
     */
    protected static function visibleLabels(): array
    {
        return [
            'Component 1',
            'Component 2',
            'Component 3',
            'Component 4',
            'Component 5',
            'Component 6',
            'Component 7',
            'Component 8',
            'Frequency',
            'Risk Level',
            'Legacy Category',
            'Topic',
        ];
    }

    /**
     * Get the labels that are visible on the show page.
     *
     * @return array<int, string>
     */
    protected static function visibleShowLabels(): array
    {
        return ['Description'];
    }

    public function testJsonIndex(): void
    {
        $item = AssessmentItem::factory()->create();
        $location = Location::factory()->create();
        $description = AssessmentItemDescription::factory()->create(['assessment_item_id' => $item->id, 'location_id' => $location->id]);

        $this->signIn();

        $route = route('collaborate.assessment-items.index.json', ['search' => $item->component_1]);

        $this->validateCollaborateRole($route);

        $this->get($route)
            ->assertSuccessful()
            ->assertJson([['id' => $item->id, 'title' => $item->description, 'details' => '']]);

        $route = route('collaborate.assessment-items.index.json', ['search' => $item->component_1, 'location_id' => $location->id]);

        $this->get($route)
            ->assertSuccessful()
            ->assertJson([['id' => $item->id, 'title' => $item->description, 'details' => $description->description]]);
    }

    public function testActions(): void
    {
        $active = AssessmentItem::factory()->create();
        $inactive = AssessmentItem::factory()->create();

        $this->collaboratorSignIn($this->collaborateSuperUser());

        $this->get(route('collaborate.assessment-items.index'))
            ->assertSuccessful()
            ->assertSee($active->question)
            ->assertSee($inactive->question);

        $payload = ["actions-checkbox-{$inactive->id}" => true];

        $this->followingRedirects()
            ->post(route('collaborate.assessment-items.actions', ['action' => 'archive']), $payload)
            ->assertSessionHasNoErrors()
            ->assertSuccessful()
            ->assertSee($active->question)
            ->assertSee($inactive->question);

        $this->get(route('collaborate.assessment-items.index', ['archived' => 'Yes']))
            ->assertSuccessful()
            ->assertDontSee($active->question)
            ->assertSee($inactive->question);

        $this->get(route('collaborate.assessment-items.index', ['archived' => 'No']))
            ->assertSuccessful()
            ->assertSee($active->question)
            ->assertDontSee($inactive->question);

        $this->followingRedirects()
            ->post(route('collaborate.assessment-items.actions', ['action' => 'restore']), $payload)
            ->assertSessionHasNoErrors()
            ->assertSuccessful()
            ->assertSee($active->question)
            ->assertSee($inactive->question);

        $this->get(route('collaborate.assessment-items.index', ['archived' => 'Yes']))
            ->assertSuccessful()
            ->assertDontSee($active->question)
            ->assertDontSee($inactive->question);

        $this->get(route('collaborate.assessment-items.index', ['archived' => 'No']))
            ->assertSuccessful()
            ->assertSee($active->question)
            ->assertSee($inactive->question);
    }
}
