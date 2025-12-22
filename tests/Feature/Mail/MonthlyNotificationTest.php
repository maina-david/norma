<?php

namespace Tests\Feature\Mail;

use App\Mail\Notify\MonthlyNotification;
use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Notify\LegalUpdate;
use Tests\TestCase;

class MonthlyNotificationTest extends TestCase
{
    /**
     * @return void
     */
    public function testItRendersCorrectly(): void
    {
        $update = LegalUpdate::factory()->create();
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create();
        $norma = Norma::factory()->create(['organisation_id' => $organisation->id]);

        $user->organisations()->attach($organisation->id);
        $user->normas()->attach($norma->id);
        $update->normas()->attach($norma->id);
        $update->users()->attach($user->id, ['email_sent' => true]);

        $mail = new MonthlyNotification($user, [$update->id]);

        $mail->assertSeeInHtml('Hi' . `<span style="color: #014E20; font-weight: 600;">{{ $user->fname }}</span>`, false);
        $mail->assertSeeInHtml('Here is your monthly overview of legal updates relevant to your organisation');
        $mail->assertSeeInHtml('Go To Updates Module');
        $mail->assertSeeInHtml('See all your updates below:');
        $mail->assertSeeInHtml($update->title, false);
        $mail->assertSeeInHtml('Changes to your Norma Stream(s)');
        $mail->assertSeeInHtml('If you do not wish to receive these emails, please click here to');
    }
}
