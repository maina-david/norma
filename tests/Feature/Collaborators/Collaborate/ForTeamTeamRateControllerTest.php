<?php

namespace Tests\Feature\Collaborators\Collaborate;

use App\Models\Collaborators\Team;
use App\Models\Collaborators\TeamRate;
use App\Models\Workflows\TaskType;
use Tests\TestCase;

class ForTeamTeamRateControllerTest extends TestCase
{
    public function testIndex(): void
    {
        $team = Team::factory()->create();
        $teamRates = TeamRate::factory(3)->for($team)->create();
        $routeName = 'collaborate.collaborators.teams.team-rates.index';
        $route = route($routeName, ['team' => $team]);
        $this->validateAuthGuard($route);
        $this->collaboratorSignIn();
        $this->validateCollaborateRole($route);

        $response = $this->get($route)->assertSuccessful();
        $response->assertSee($teamRates[0]->taskType->name);
        $response->assertSee($teamRates[0]->rate_per_unit);
        $response->assertSee($teamRates[1]->taskType->name);
        $response->assertSee($teamRates[1]->rate_per_unit);
    }

    public function testCrud(): void
    {
        $team = Team::factory()->create();
        $routeName = 'collaborate.collaborators.teams.team-rates.create';
        $route = route($routeName, ['team' => $team]);
        $this->validateAuthGuard($route);
        $this->collaboratorSignIn();
        $this->validateCollaborateRole($route);

        $this->get($route)
            ->assertSuccessful()
            ->assertSee('Task Type')
            ->assertSee('Rate per Unit')
            ->assertSee('Unit Type (Singular)')
            ->assertSee('Unit Type (Plural)');

        $teamRate = TeamRate::factory()->for($team)->make();
        $routeName = 'collaborate.collaborators.teams.team-rates.store';
        $this->followingRedirects()
            ->post(route($routeName, ['team' => $team]), $teamRate->toArray())
            ->assertSuccessful();
        $teamRate = $team->rates->first();

        // can't create a rate with the same task type
        $this->withExceptionHandling()
            ->post(route($routeName, ['team' => $team]), $teamRate->toArray())
            ->assertInvalid();

        $routeName = 'collaborate.collaborators.teams.team-rates.edit';
        $this->get(route($routeName, ['team' => $team, 'team_rate' => $teamRate->id]))
            ->assertSuccessful()
            ->assertSee('Task Type')
            ->assertSee('Rate per Unit')
            ->assertSee('Unit Type (Singular)')
            ->assertSee('Unit Type (Plural)');

        $taskType = TaskType::factory()->create();
        $teamRate2 = TeamRate::factory()->for($team)->for($taskType)->create();
        $routeName = 'collaborate.collaborators.teams.team-rates.update';
        // can't edit a rate with a task type that already exist
        $this->withExceptionHandling()
            ->put(route($routeName, ['team' => $team, 'team_rate' => $teamRate->id]), ['for_task_type_id' => $taskType->id])
            ->assertInvalid();

        $this->withExceptionHandling()
            ->put(route($routeName, ['team' => $team, 'team_rate' => $teamRate->id]), ['rate_per_unit' => 'asdlfkjasdlfkjsf'])
            ->assertInvalid();

        $routeName = 'collaborate.collaborators.teams.team-rates.destroy';
        $response = $this->followingRedirects()
            ->delete(route($routeName, ['team' => $team, 'team_rate' => $teamRate->id]))
            ->assertSuccessful();
    }

    public function testUpdate(): void
    {
        $team = Team::factory()->create();
        $teamRate = TeamRate::factory()->for($team)->create();
        $routeName = 'collaborate.collaborators.teams.team-rates.update';
        $route = route($routeName, ['team' => $team, 'team_rate' => $teamRate->id]);
        $this->validateAuthGuard($route, 'put');
        $this->collaboratorSignIn();
        $this->validateCollaborateRole($route, 'put');

        $teamRate->rate_per_unit = 10000.5;
        $this->followingRedirects()
            ->put(route($routeName, ['team' => $team, 'team_rate' => $teamRate->id]), $teamRate->toArray())
            ->assertSuccessful();
        $this->assertSame(10000.5, $teamRate->refresh()->rate_per_unit);
    }
}
