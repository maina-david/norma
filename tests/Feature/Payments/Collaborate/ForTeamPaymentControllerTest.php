<?php

namespace Tests\Feature\Payments\Collaborate;

use App\Models\Collaborators\Team;
use App\Models\Payments\Payment;
use Illuminate\Support\Carbon;
use Tests\Feature\Traits\HasVisibleFieldAssertions;
use Tests\TestCase;

class ForTeamPaymentControllerTest extends TestCase
{
    use HasVisibleFieldAssertions;

    protected static function visibleFields(): array
    {
        return ['amount'];
    }

    public function testIndex(): void
    {
        $this->travelTo(Carbon::parse('2022-01-01 12:00:00'));
        $team = Team::factory()->create();
        $payments = Payment::factory(3)->create(['team_id' => $team->id]);
        $payments[2]->update(['team_id' => null]);

        $routeName = 'collaborate.collaborators.teams.payments.index';
        $route = route($routeName, ['team' => $team]);
        $this->validateAuthGuard($route);
        $user = $this->signIn();
        $this->validateCollaborateRole($route);

        $response = $this->get($route)->assertSuccessful();
        $this->assertSeeVisibleFields($payments[0], $response);
        $this->assertSeeVisibleFields($payments[1], $response);
    }
}
