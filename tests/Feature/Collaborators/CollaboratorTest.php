<?php

namespace Tests\Feature\Collaborators;

use App\Enums\Collaborators\CollaboratorStatus;
use App\Enums\Collaborators\ContractType;
use App\Enums\Collaborators\DocumentType;
use App\Enums\Collaborators\LanguageProficiency;
use App\Enums\Collaborators\YearOfStudy;
use App\Http\ResourceActions\Collaborators\ActivateCollaborator;
use App\Http\ResourceActions\Collaborators\DeactivateCollaborator;
use App\Livewire\Collaborators\UpdateResidencyModal;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Auth\UserRole;
use App\Models\Collaborators\Collaborator;
use App\Models\Collaborators\Group;
use App\Models\Collaborators\GroupUser;
use App\Models\Collaborators\Profile;
use App\Models\Collaborators\Team;
use App\Models\Storage\Collaborate\Attachment;
use Livewire;
use Tests\Feature\Abstracts\CrudTestCase;

class CollaboratorTest extends CrudTestCase
{
    /** @var string */
    protected string $sortBy = 'fname';

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return Collaborator::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.collaborators';
    }

    /**
     * The list of database columns that should be visible on the pages.
     *
     * @return string[]
     */
    protected static function visibleFields(): array
    {
        return ['fname', 'university'];
    }

    /**
     * The list of database columns that should be visible on the pages with forms.
     *
     * @return string[]
     */
    protected static function visibleLabels(): array
    {
        return ['Nationality', 'University'];
    }

    /**
     * A list of actions to exclude from testing.
     * Options: index, create, store, edit, update, destroy.
     *
     * @return array
     */
    protected static function excludeActions(): array
    {
        return [
            'create', 'store', 'edit', 'update', 'destroy',
        ];
    }

    public function testActivationOfCollaborators(): void
    {
        $collaborators = Collaborator::factory()->count(10)->create(['active' => true]);
        $route = route('collaborate.collaborators.actions');

        $payload = collect();

        $collaborators->each(function ($coll) use ($payload) {
            $payload->put("actions-checkbox-{$coll->id}", true);
            $this->assertTrue($coll->active);
        });

        $this->validateAuthGuard($route);

        $this->signIn();

        $this->from(route('collaborate.collaborators.index'));

        $this->validateCollaborateRole($route, 'post');

        $collaborators->each(function ($coll) {
            $this->assertTrue($coll->refresh()->active);
        });

        $payload = $payload->toArray();
        $payload['action'] = (new DeactivateCollaborator())->actionId();

        $this->from(route('collaborate.collaborators.index'))
            ->followingRedirects()
            ->post($route, $payload)
            ->assertSuccessful();

        $collaborators->each(function ($coll) {
            $this->assertFalse($coll->refresh()->active);
        });

        $payload['action'] = (new ActivateCollaborator())->actionId();

        $this->from(route('collaborate.collaborators.index'))
            ->followingRedirects()
            ->post($route, $payload)
            ->assertSuccessful();

        $collaborators->each(function ($coll) {
            $this->assertTrue($coll->refresh()->active);
        });
    }

    /**
     * @return void
     */
    public function testCreation(): void
    {
        $this->signIn($this->collaborateSuperUser());
        $group = Group::factory()->create();

        $payload = [
            'fname' => 'Zebra',
            'sname' => 'Whoop',
            'email' => 'zebracorn@norma.awesome',
            'team_id' => '-1',
            'groups' => [$group->id],
        ];

        $this->assertDatabaseMissing(Collaborator::class, ['fname' => 'Zebra', 'sname' => 'Whoop']);
        $this->assertDatabaseMissing(Team::class, ['title' => 'Zebra Whoop']);
        $this->assertDatabaseMissing(GroupUser::class, ['group_id' => $group->id]);

        $response = $this->post(route('collaborate.collaborators.store', $payload));

        $this->assertDatabaseHas(Collaborator::class, ['fname' => 'Zebra', 'sname' => 'Whoop']);
        $this->assertDatabaseHas(Team::class, ['title' => 'Zebra Whoop']);
        $this->assertDatabaseHas(GroupUser::class, ['group_id' => $group->id]);

        $collaborator = Collaborator::where('email', 'zebracorn@norma.awesome')->with(['profile'])->first();
        $team = Team::where('title', 'Zebra Whoop')->first();

        $this->assertSame($team->id, $collaborator->profile->team_id);

        $response->assertRedirect(route('collaborate.teams.edit', $team->id));

        $payload = [
            'fname' => 'Zebra2',
            'sname' => 'Whoop2',
            'email' => 'zebracorn2@norma.awesome',
            'team_id' => $team,
            'groups' => [$group->id],
        ];

        $this->post(route('collaborate.collaborators.store', $payload));

        $this->assertDatabaseHas(Collaborator::class, ['fname' => 'Zebra2', 'sname' => 'Whoop2']);
        $this->assertDatabaseMissing(Team::class, ['title' => 'Zebra2 Whoop2']);
        $this->assertDatabaseHas(GroupUser::class, ['group_id' => $group->id]);

        $collaborator = Collaborator::where('email', 'zebracorn2@norma.awesome')->with(['profile'])->first();

        $this->assertSame($team->id, $collaborator->profile->team_id);
    }

    /**
     * @return void
     */
    public function testRoleManagement(): void
    {
        $collaborator = Collaborator::factory()->create();
        $route = route('collaborate.collaborators.roles.update', $collaborator->id);

        $this->validateAuthGuard($route, 'put');

        $this->signIn();

        $this->from(route('collaborate.collaborators.index'));

        $this->validateCollaborateRole($route, 'put');

        $role = Role::factory()->collaborate()->create();

        $this->put($route, ['roles' => [$role->id]])
            ->assertRedirect(route('collaborate.collaborators.show', ['collaborator' => $collaborator->id, 'tab' => 'access']));

        $this->assertDatabaseHas(UserRole::class, ['user_id' => $collaborator->id, 'role_id' => $role->id]);

        $newRole = Role::factory()->collaborate()->create();

        $this->put($route, ['roles' => [$newRole->id]])
            ->assertRedirect(route('collaborate.collaborators.show', ['collaborator' => $collaborator->id, 'tab' => 'access']));

        $this->assertDatabaseHas(UserRole::class, ['user_id' => $collaborator->id, 'role_id' => $newRole->id]);
        $this->assertDatabaseMissing(UserRole::class, ['user_id' => $collaborator->id, 'role_id' => $role->id]);

        $this->put($route, ['roles' => []])
            ->assertRedirect(route('collaborate.collaborators.show', ['collaborator' => $collaborator->id, 'tab' => 'access']));

        $this->assertDatabaseMissing(UserRole::class, ['user_id' => $collaborator->id, 'role_id' => $newRole->id]);
        $this->assertDatabaseMissing(UserRole::class, ['user_id' => $collaborator->id, 'role_id' => $role->id]);
    }

    public function testFilters(): void
    {
        $role = Role::factory()->create();
        $collaborator = Collaborator::factory()->hasAttached($role)->create(['active' => true]);

        $profile = $collaborator->profile;
        $profile->documents()->create([
            'type' => DocumentType::CONTRACT,
            'subtype' => ContractType::zeroHour()->value,
            'active' => true,
            'approved_at' => now(),
            'attachment_id' => Attachment::factory()->create()->id,
        ]);

        $profile->languages()->create([
            'language_code' => 'eng',
            'proficiency' => LanguageProficiency::advanced()->value,
        ]);

        $profile->update([
            'year_of_study' => YearOfStudy::PARALEGAL->value,
        ]);

        $this->signIn($this->collaborateSuperUser());

        $profile->update([
            'nationality' => 'KE',
            'current_residency' => 'KE',
        ]);

        $filters = [
            'language' => 'eng',
            'proficiency' => LanguageProficiency::advanced()->value,
            'year' => YearOfStudy::PARALEGAL->value,
            'contract' => ContractType::zeroHour()->value,
            'status' => CollaboratorStatus::ACTIVE->value,
            'restricted-visa' => 'No',
            'expired-visa' => 'No',
            'role' => $role->id,
            'nationality' => 'KE',
            'residency' => 'KE',
        ];

        $this->get(route('collaborate.collaborators.index', $filters))
            ->assertSuccessful()
            ->assertSee($collaborator->full_name);

        $this->get(route('collaborate.collaborators.index', ['contract' => ContractType::standard()->value, 'expired-visa' => 'Yes']))
            ->assertSuccessful()
            ->assertDontSee($collaborator->full_name);
    }

    /**
     * @return void
     */
    public function testImporting(): void
    {
        $this->signIn($this->collaborateSuperUser());
        $user = User::factory()->create();

        $this->assertDatabaseMissing(Profile::class, ['user_id' => $user->id]);

        $this->post(route('collaborate.collaborators.import'), ['email' => $user->email])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('collaborate.collaborators.show', ['collaborator' => $user->id, 'tab' => 'access']));

        $this->assertDatabaseHas(Profile::class, ['user_id' => $user->id]);

        $this->from(route('collaborate.collaborators.create'))
            ->post(route('collaborate.collaborators.import'), ['email' => $user->email])
            ->assertRedirect(route('collaborate.collaborators.create'));
    }

    public function testResidencyModal(): void
    {
        $user = $this->signIn($this->collaborateSuperUser());

        $this->assertDatabaseHas(Profile::class, ['user_id' => $user->id, 'residency_confirmed_at' => null]);

        Livewire::test(UpdateResidencyModal::class)
            ->assertSee('Please confirm if your current residency and address align with the details in your profile')
            ->call('confirmResidency');

        $this->assertDatabaseMissing(Profile::class, ['user_id' => $user->id, 'residency_confirmed_at' => null]);
    }
}
