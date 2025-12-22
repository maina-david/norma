<?php

namespace Tests\Feature\Console\Notify;

use App\Mail\Notify\DailyNotification;
use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Notify\LegalUpdate;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendDailyNotificationEmailsTest extends TestCase
{
    /**
     * @return void
     */
    public function testItSendsTheNotificationToUsers(): void
    {
        $update = LegalUpdate::factory()->create();
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create();
        $norma = Norma::factory()->create(['organisation_id' => $organisation->id]);

        $user->organisations()->attach($organisation->id);
        $user->normas()->attach($norma->id);
        $update->normas()->attach($norma->id);
        $update->users()->attach($user->id);

        Mail::fake();

        Mail::assertNothingOutgoing();

        Artisan::call('norma:daily-notification-emails');

        Mail::assertQueued(function (DailyNotification $mail) use ($user) {
            return $mail->user->id === $user->id;
        });
    }
}
