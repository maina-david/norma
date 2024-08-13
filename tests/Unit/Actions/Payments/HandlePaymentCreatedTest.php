<?php

namespace Tests\Unit\Actions\Payments;

use App\Actions\Payments\HandlePaymentCreated;
use App\Models\Auth\User;
use App\Models\Collaborators\Collaborator;
use App\Models\Collaborators\Profile;
use App\Models\Collaborators\Team;
use App\Models\Payments\Payment;
use App\Notifications\Payments\PaymentMade;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class HandlePaymentCreatedTest extends TestCase
{
    public function testHandle(): void
    {
        Notification::fake();

        $team = Team::factory()->create();
        $payment = Payment::withoutEvents(function () use ($team) {
            return Payment::factory()->for($team)->create();
        });
        $user = Collaborator::factory()->create();
        $profile = Profile::factory()->for(User::find($user->id))->create(['team_id' => $team->id, 'is_team_admin' => false]);
        app(HandlePaymentCreated::class)->handle($payment);
        Notification::assertNothingSent();

        $profile->update(['is_team_admin' => true]);

        app(HandlePaymentCreated::class)->handle($payment);
        $this->travel(10)->minutes();

        Notification::assertSentTo(
            $user,
            PaymentMade::class,
        );
    }
}
