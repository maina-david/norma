<?php

namespace Tests\Feature\Collaborators\Collaborate;

use App\Models\Collaborators\Team;
use App\Models\Payments\PaymentRequest;
use Tests\TestCase;

class TeamOutstandingPaymentRequestsControllerTest extends TestCase
{
    public function testIndex(): void
    {
        $teams = Team::factory(3)->create();
        PaymentRequest::factory(2)->for($teams[0])->create(['paid' => false]);
        PaymentRequest::factory(2)->for($teams[1])->create(['paid' => true]);
        $this->travelBack();

        $routeName = 'collaborate.collaborators.teams.payment-requests.outstanding.index';
        $route = route($routeName);
        $this->validateAuthGuard($route);
        $this->signIn();
        $this->validateCollaborateRole($route);

        $response = $this->get($route)->assertSuccessful();
        $response->assertSee($teams[0]->title);
        // no outstanding requests, shouldn't see
        $response->assertDontSee($teams[1]->title);
        $response->assertDontSee($teams[2]->title);
    }
}
