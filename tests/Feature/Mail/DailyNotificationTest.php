<?php

namespace Tests\Feature\Mail;

use App\Mail\Notify\DailyNotification;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Geonames\Location;
use App\Models\Notify\LegalUpdate;
use Tests\TestCase;

class DailyNotificationTest extends TestCase
{
    /**
     * @return void
     */
    public function testItRendersCorrectly(): void
    {
        $location = Location::factory()->create();
        $location->update(['location_country_id' => $location->id]);
        $childLocation = Location::factory()->create(['parent_id' => $location->id, 'level' => 2, 'location_country_id' => $location->id]);
        $location->ancestors()->attach($location, ['depth' => 0]);
        $childLocation->ancestors()->attach($location, ['depth' => 1]);
        $update = LegalUpdate::factory()->create(['primary_location_id' => $childLocation->id]);
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create();
        $libryo = Libryo::factory()->create(['organisation_id' => $organisation->id]);

        $user->organisations()->attach($organisation->id);
        $user->libryos()->attach($libryo->id);
        $update->libryos()->attach($libryo->id);
        $update->users()->attach($user->id);

        $mail = new DailyNotification($user, [$update->id]);

        $mail->assertSeeInHtml('Hi' . `<span style="color: #014E20; font-weight: 600;">{{ $user->fname }}</span>`, false);
        $mail->assertDontSeeInHtml('Here is your monthly overview of legal updates relevant to your organisation');
        $mail->assertSeeInHtml('Go To Updates Module');
        $mail->assertSeeInHtml('See all your updates below:');
        $mail->assertSeeInHtml($update->title, false);
        $mail->assertSeeInHtml('Changes to your Libryo Stream(s)');
        $mail->assertSeeInHtml('If you do not wish to receive these emails, please click here to');
        $mail->assertSeeInHtml($location->title, false);
        $mail->assertSeeInHtml($childLocation->title, false);
        $mail->assertSeeInHtml($update->publication_date->format('j F Y'));
        $mail->assertSeeInHtml($update->effective_date->format('j F Y'));
        $mail->assertSeeInHtml($update->publication_number);
        $mail->assertSeeInHtml($update->publication_document_number);

        $update = LegalUpdate::factory()->create(['primary_location_id' => $location->id]);
        $update->libryos()->attach($libryo->id);
        $update->users()->attach($user->id);
        $mail = new DailyNotification($user, [$update->id]);
        $mail->assertSeeInHtml($location->title, false);
    }
}
