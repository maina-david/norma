<?php

namespace Tests\Feature\Console\Notify;

use App\Mail\Notify\MonthlyNotification;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;
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
        $libryo = Libryo::factory()->create(['organisation_id' => $organisation->id]);

        $user->organisations()->attach($organisation->id);
        $user->libryos()->attach($libryo->id);
        $update->libryos()->attach($libryo->id);
        $update->users()->attach($user->id, ['email_sent' => true]);

        Mail::fake();

        Mail::assertNothingOutgoing();

        Artisan::call('libryo:monthly-notification-emails');

        Mail::assertQueued(function (MonthlyNotification $mail) use ($user) {
            return $mail->user->id === $user->id;
        });
    }
}
