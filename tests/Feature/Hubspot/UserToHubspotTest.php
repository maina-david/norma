<?php

namespace Tests\Feature\Hubspot;

use App\Enums\Auth\UserType;
use App\Http\Services\Hubspot\Actions\UserToHubspotContact;
use App\Models\Auth\User;
use App\Models\Customer\Team;
use App\Models\Notify\LegalUpdate;
use App\Models\Partners\WhiteLabel;
use Tests\TestCase;

class UserToHubspotTest extends TestCase
{
    /**
     * @return void
     */
    public function testItCanGenerateHubspotProperties(): void
    {
        $update = LegalUpdate::factory()->create();
        /** @var User $user */
        $user = User::factory()->create();
        $user->legalUpdates()->attach($update->id);
        /** @var Team $team */
        $team = Team::factory()->create();
        $team->users()->attach($user->id);
        $team->organisation->users()->attach($user->id);

        $this->assertEquals(1, $user->teams()->count());
        $this->assertEquals(1, $user->organisations()->count());

        $properties = UserToHubspotContact::convert($user);
        $properties = collect($properties->getProperties());

        $userTypes = [
            UserType::demo()->value => 'Demo User',
            UserType::other()->value => 'Other',
            UserType::partner()->value => 'Partner',
            UserType::staff()->value => 'Staff',
        ];

        $lifecycles = [
            UserType::free()->value => 'lead',
            UserType::partner()->value => 'other',
        ];

        $this->assertSame($user->email, $properties->get('original_email'));
        $this->assertSame($user->email, $properties->get('email'));
        $this->assertSame($user->fname, $properties->get('firstname'));
        $this->assertSame($user->sname, $properties->get('lastname'));
        $this->assertSame('Yes', $properties->get('from_libryo_api'));
        $this->assertSame('active', $properties->get('libryo_status'));
        $this->assertSame(1, $properties->get('legal_updates_unread'));
        $this->assertSame(1, $properties->get('total_number_of_legal_updates'));
        $this->assertSame(0, $properties->get('legal_updates_read'));
        $this->assertSame(0, $properties->get('legal_updates_read_and_understood'));
        $this->assertSame(0, $properties->get('libryo_know_your_law_activities'));
        $this->assertSame(0, $properties->get('successful_searches'));
        $this->assertSame(0, $properties->get('number_of_activities_all_time_'));
        $this->assertSame(0, $properties->get('number_of_activities_yearly_'));
        $this->assertSame(0, $properties->get('number_of_activities'));
        $this->assertSame($userTypes[$user->user_type] ?? 'User', $properties->get('user_type'));
        $this->assertSame($lifecycles[$user->user_type] ?? 'customer', $properties->get('lifecyclestage'));
        $this->assertSame('Active', $properties->get('libryo_user_status'));
        $this->assertSame('Disengaged', $properties->get('journey_to_core'));
        $this->assertSame('No', $properties->get('covid_19_campaign_rubicon'));
        $this->assertSame(UserType::free()->is($user->user_type) ? 'Yes' : 'No', $properties->get('free_user'));
        $this->assertSame($user->created_at->startOfDay()->getTimestamp() * 1000, $properties->get('date_added_to_libryo'));
        $this->assertSame($team->organisation->title, $properties->get('libryo_organisation'));
        $this->assertSame($team->title, $properties->get('libryo_team'));
    }

    /**
     * @return void
     */
    public function testCheckRubiconUserValue(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var Team $team */
        $team = Team::factory()->create();
        $team->users()->attach($user->id);
        $team->organisation->users()->attach($user->id);

        $properties = UserToHubspotContact::convert($user);
        $properties = collect($properties->getProperties());

        $this->assertSame('No', $properties->get('covid_19_campaign_rubicon'));

        /** @var WhiteLabel $whiteLabel */
        $whiteLabel = WhiteLabel::factory(['title' => 'Rubicon', 'shortname' => 'rubicon'])->create();

        $team->organisation->update(['whitelabel_id' => $whiteLabel->id]);
        unset($team->organisation);

        $user = User::find($user->id);
        $user->update(['user_type' => UserType::free()->value]);

        $properties = UserToHubspotContact::convert($user);
        $properties = collect($properties->getProperties());

        $this->assertSame('Yes', $properties->get('covid_19_campaign_rubicon'));
    }
}
