<?php

namespace Tests\Feature\Payments\Collaborate;

use App\Models\Collaborators\Team;
use App\Models\Payments\PaymentRequest;
use Illuminate\Support\Carbon;
use Tests\Feature\Traits\HasVisibleFieldAssertions;
use Tests\TestCase;

class ForTeamOutstandingPaymentRequestsControllerTest extends TestCase
{
    use HasVisibleFieldAssertions;

    protected static function visibleFields(): array
    {
        return ['units'];
    }

    public function testIndex(): void
    {
        $this->travelTo(Carbon::parse('2022-01-01 12:00:00'));
        $team = Team::factory()->create();
        $paymentRequests = PaymentRequest::factory(3)->for($team)->create();
        $paymentRequests[2]->update(['paid' => true]);

        $routeName = 'collaborate.collaborators.teams.payment-requests.outstanding';
        $route = route($routeName, ['team' => $team]);
        $this->validateAuthGuard($route);
        $this->signIn();
        $this->validateCollaborateRole($route);

        $response = $this->get($route)->assertSuccessful();
        $this->assertSeeVisibleFields($paymentRequests[0], $response);
        $this->assertSeeVisibleFields($paymentRequests[1], $response);

        $response->assertSee($paymentRequests[0]->task->title);
        $response->assertSee($paymentRequests[1]->task->title);
        $response->assertDontSee($paymentRequests[2]->task->title);
    }

    public function testPayOutstanding(): void
    {
        $team = Team::factory()->create();
        $routeName = 'collaborate.collaborators.teams.payment-requests.outstanding.pay';
        $route = route($routeName, ['team' => $team]);
        $this->validateAuthGuard($route, 'post');
        $this->signIn();
        $this->validateCollaborateRole($route, 'post');

        $response = $this->followingRedirects()->post($route)->assertSuccessful();
    }
}
