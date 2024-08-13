<?php

namespace Tests\Feature\Workflows;

use App\Models\Auth\User;
use App\Models\Collaborators\ProductionPod;
use App\Models\Customer\Organisation;
use App\Models\Geonames\Location;
use App\Models\Ontology\LegalDomain;
use App\Models\Workflows\Project;
use Illuminate\Database\Eloquent\Collection;
use Tests\Feature\Abstracts\CrudTestCase;

class ProjectControllerTest extends CrudTestCase
{
    protected bool $collaborate = true;

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return Project::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.projects';
    }

    /**
     * The list of database columns that should be visible on the pages.
     *
     * @return string[]
     */
    protected static function visibleFields(): array
    {
        return ['title'];
    }

    /**
     * The list of database columns that should be visible on the pages with forms.
     *
     * @return string[]
     */
    protected static function visibleLabels(): array
    {
        return ['Title'];
    }

    /**
     * Test the show page for a single Librarian Project can be accessed by an authorized user.
     *
     * @return void
     */
    public function testItRendersTheShowPage(): void
    {
        $user = $this->signIn($this->collaborateSuperUser());
        $project = Project::factory()->create();

        $response = $this->actingAs($user)->get(route('collaborate.projects.show', $project->id));

        $response->assertOk();
        $response->assertSee($project->title);
    }

    public function testItFiltersCorrectly(): void
    {
        /** @var User */
        $owner = User::factory()->create();
        $owner2 = User::factory()->create();
        /** @var Location */
        $location = Location::factory()->create();
        /** @var LegalDomain */
        $domain = LegalDomain::factory()->create();
        /** @var Organisation */
        $organisation = Organisation::factory()->create();
        /** @var ProductionPod */
        $pod = ProductionPod::factory()->create();

        /** @var Collection<Project> */
        $filtered = Project::factory()->count(2)->create();

        $filtered[0]?->legalDomains()->attach($domain);
        $filtered[0]?->update([
            'organisation_id' => $organisation->id,
            'location_id' => $location->id,
            'owner_id' => $owner->id,
            'production_pod_id' => $pod->id,
        ]);

        $this->signIn($this->collaborateSuperUser());

        $response = $this->get(route('collaborate.projects.index', ['jurisdiction' => $location->id]))
            ->assertSuccessful()
            ->assertSee($filtered[0]?->title)
            ->assertDontSee($filtered[1]?->title);

        $response = $this->get(route('collaborate.projects.index', ['owner' => $owner->id]))
            ->assertSuccessful()
            ->assertSee($filtered[0]?->title)
            ->assertDontSee($filtered[1]?->title);

        $response = $this->get(route('collaborate.projects.index', ['domains' => [$domain->id]]))
            ->assertSuccessful()
            ->assertSee($filtered[0]?->title)
            ->assertDontSee($filtered[1]?->title);

        $response = $this->get(route('collaborate.projects.index', ['organisation' => $organisation->id]))
            ->assertSuccessful()
            ->assertSee($filtered[0]?->title)
            ->assertDontSee($filtered[1]?->title);
        $response = $this->get(route('collaborate.projects.index', ['pod' => $pod->id]))
            ->assertSuccessful()
            ->assertSee($filtered[0]?->title)
            ->assertDontSee($filtered[1]?->title);
    }
}
