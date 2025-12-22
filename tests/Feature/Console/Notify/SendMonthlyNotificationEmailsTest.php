<?php

namespace Tests\Feature\Console\Notify;

use App\Mail\Notify\MonthlyNotification;
use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Notify\LegalUpdate;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendMonthlyNotificationEmailsTest extends TestCase
{
    /**
     * @return void
     */
    public function testItSendsTheNotificationToUsers(): void
    {
        $update = LegalUpdate::factory()->create(['release_at' => now()->subMonth()]);
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create();
        $norma = Norma::factory()->create(['organisation_id' => $organisation->id]);

        $user->organisations()->attach($organisation->id);
        $user->normas()->attach($norma->id);
        $update->normas()->attach($norma->id);
        $update->users()->attach($user->id, ['email_sent' => true]);

        Mail::fake();

        Mail::assertNothingOutgoing();

        Artisan::call('norma:monthly-notification-emails');

        Mail::assertQueued(function (MonthlyNotification $mail) use ($user) {
            return $mail->user->id === $user->id;
        });
    }
}
