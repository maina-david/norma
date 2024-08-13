<?php

namespace Tests\Unit\Notifications\Payments;

use App\Models\Auth\User;
use App\Models\Collaborators\Team;
use App\Models\Payments\Payment;
use App\Notifications\Payments\PaymentMade;
use Tests\TestCase;

class PaymentMadeTest extends TestCase
{
    public function testItRendersTheMail(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $payment = Payment::withoutEvents(function () use ($team) {
            return Payment::factory()->for($team)->create();
        });
        $message = (new PaymentMade($payment->id))->toMail($user);
        $rendered = $message->render();

        $this->assertStringContainsString($user->fname, $rendered);
        $this->assertNotEmpty($message->rawAttachments);
    }
}
