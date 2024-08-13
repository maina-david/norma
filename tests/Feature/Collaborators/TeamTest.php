<?php

namespace Tests\Feature\Collaborators;

use App\Models\Collaborators\Profile;
use App\Models\Collaborators\Team;
use Tests\Feature\Abstracts\CrudTestCase;

class TeamTest extends CrudTestCase
{
    /** @var string */
    protected string $sortBy = 'title';

    protected bool $collaborate = true;

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return Team::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.teams';
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
        $arr = static::getBankAccountFields();

        return ['Name', 'Currency', 'Automatically Pay', 'Transferwise ID', ...$arr];
    }

    public function testMyBilling(): void
    {
        $team = Team::factory()->create();
        $routeName = 'collaborate.collaborators.teams.billing.edit';
        $route = route($routeName);
        $this->validateAuthGuard($route);
        $user = $this->signIn();
        $profile = Profile::factory()->for($user)->create(['team_id' => $team->id, 'is_team_admin' => true]);
        $this->validateCollaborateRole($route);

        $response = $this->get($route)->assertSuccessful();
        foreach (static::getBankAccountFields() as $field) {
            $response->assertSee($field);
        }
    }

    public function testMyBillingUpdate(): void
    {
        $team = Team::factory()->create();
        $routeName = 'collaborate.collaborators.teams.billing.update';
        $route = route($routeName);
        $this->validateAuthGuard($route, 'put');
        $user = $this->signIn($this->collaborateNormalUser());
        $user->profile->update(['team_id' => $team->id, 'is_team_admin' => true]);

        $response = $this->followingRedirects()->put($route, $team->toArray())
            ->assertSuccessful();
    }

    public function testMyPayments(): void
    {
        $team = Team::factory()->create();
        $routeName = 'collaborate.collaborators.teams.my-payments.index';
        $route = route($routeName);
        $this->validateAuthGuard($route);
        $user = $this->signIn();
        $profile = Profile::factory()->for($user)->create(['team_id' => $team->id, 'is_team_admin' => true]);
        $this->validateCollaborateRole($route);
    }

    private static function getBankAccountFields(): array
    {
        return [
            'Bank Name', 'Branch Code (Sort Code)',
            'Bank Physical Address', 'Bank Account Name', 'Account Number',
            'Bank Account Currency', 'International Bank Account Number (IBAN)', 'Swift Code',
            'Other billing instructions', 'Billing Address',
        ];
    }
}
