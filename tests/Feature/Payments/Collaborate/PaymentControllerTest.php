<?php

namespace Tests\Feature\Payments\Collaborate;

use App\Actions\Payments\GenerateTransferWiseExport;
use App\Models\Collaborators\Collaborator;
use App\Models\Collaborators\Team;
use App\Models\Payments\Payment;
use App\Models\Payments\PaymentRequest;
use Illuminate\Support\Carbon;
use Mockery\MockInterface;
use Tests\Feature\Traits\HasVisibleFieldAssertions;
use Tests\TestCase;

class PaymentControllerTest extends TestCase
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

        $routeName = 'collaborate.payments.payments.index';
        $route = route($routeName);
        $this->validateAuthGuard($route);
        $this->signIn();
        $this->validateCollaborateRole($route);

        $response = $this->get($route)->assertSuccessful();
        $this->assertSeeVisibleFields($payments[0], $response);
        $this->assertSeeVisibleFields($payments[1], $response);

        $response = $this->get(route($routeName, ['to' => '2020-01-01']))->assertSuccessful();
        $this->assertDontSeeVisibleFields($payments[0], $response);

        $response = $this->get(route($routeName, ['from' => '2022-05-05']))->assertSuccessful();
        $this->assertDontSeeVisibleFields($payments[0], $response);

        $response = $this->get(route($routeName, ['from' => '2021-01-01']))->assertSuccessful();
        $this->assertSeeVisibleFields($payments[0], $response);

        $response = $this->get(route($routeName, ['search' => $team->title]))->assertSuccessful();
        $this->assertSeeVisibleFields($payments[0], $response);
        $this->assertDontSeeVisibleFields($payments[2], $response);
    }

    public function testShow(): void
    {
        $amount = 10.5;
        $paid = 20.5;
        $currency = 'GBP';
        $payment = Payment::factory()->create(['target_amount' => $amount, 'target_currency' => $currency]);
        $paymentRequests = PaymentRequest::factory(2)->create(['payment_id' => $payment->id, 'amount_paid' => $paid]);
        $routeName = 'collaborate.payments.payments.show';
        $route = route($routeName, ['payment' => $payment]);
        $this->validateAuthGuard($route);
        $this->signIn();
        $this->validateCollaborateRole($route);

        $response = $this->get($route)->assertSuccessful();
        $response->assertSee($amount);
        $response->assertSee($currency);
        $response->assertSee($paymentRequests[0]->units);
        $response->assertSee($paymentRequests[1]->units);
        $response->assertSee($paymentRequests[0]->amount_paid);
        $response->assertSee($paymentRequests[1]->amount_paid);
    }

    public function testDownloadInvoice(): void
    {
        $amount = 10.5;
        $paid = 20.5;
        $currency = 'GBP';
        $team = Team::factory()->create();
        $payment = Payment::factory()->create(['target_amount' => $amount, 'target_currency' => $currency, 'team_id' => $team->id]);
        $paymentRequests = PaymentRequest::factory(2)->for($team)->create(['payment_id' => $payment->id, 'amount_paid' => $paid]);
        $routeName = 'collaborate.payments.payments.invoice.download';
        $route = route($routeName, ['payment' => $payment]);
        $this->validateAuthGuard($route);
        $user = $this->signIn($this->collaborateNormalUser());

        $response = $this->withExceptionHandling()
            ->get($route)
            ->assertForbidden();

        $user = Collaborator::with('profile')->find($user->id);
        $user->profile->update(['team_id' => $team->id]);

        $response = $this->withExceptionHandling()
            ->get($route)
            ->assertForbidden();

        $user->profile->update(['is_team_admin' => true]);
        $response = $this->get($route)
            ->assertDownload();

        $this->signIn();
        $this->validateCollaborateRole($route);
    }

    public function testGenerateCSV(): void
    {
        $this->partialMock(GenerateTransferWiseExport::class, function (MockInterface $mock) {
            $rows = collect([['name', 'amount'], ['John Doe', 10.00], ['Jane Doe', 20.00]]);
            $mock->shouldReceive('handle')->andReturn($rows);
        });
        $routeName = 'collaborate.payments.payments.export';
        $route = route($routeName);
        $this->validateAuthGuard($route, 'post');
        $user = $this->signIn($this->collaborateNormalUser());

        $response = $this->withExceptionHandling()
            ->post($route)
            ->assertForbidden();

        $role = $this->collaborateSuper();
        $user->roles()->attach($role->id);
        $user->flushComputedPermissions();

        $this->followingRedirects()
            ->post($route, ['start_date' => '2022-01-01'])
            ->assertSuccessful()
            ->assertHeader('Content-Disposition', 'attachment; filename="Payment CSV.csv"')
            ->assertSee('John Doe')
            ->assertSee('Jane Doe');
    }
}
