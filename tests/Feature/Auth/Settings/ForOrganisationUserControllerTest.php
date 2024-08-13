<?php

namespace Tests\Feature\Auth\Settings;

use App\Exports\Customer\Settings\UserExport;
use App\Models\Auth\User;
use App\Models\Customer\Organisation;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Feature\Settings\SettingsTestCase;

class ForOrganisationUserControllerTest extends SettingsTestCase
{
    public function testIndexForbiddenForNonSuperAdmin(): void
    {
        $org = Organisation::factory()->create();
        $route = route('my.settings.users.for.organisation.index', ['organisation' => $org->id]);
        $user = $this->assertForbiddenForNonAdmin($route, 'get');

        $user->organisations()->attach($org, ['is_admin' => true]);

        $response = $this->withExceptionHandling()->get($route);
        $response->assertForbidden();
    }

    public function testIndexAllOrgs(): void
    {
        [$org, $user1, $user2, $user3] = $this->prepIndexData();
        $route = route('my.settings.users.for.organisation.index', ['organisation' => $org->id]);

        $this->signIn($this->mySuperUser());
        $this->withAllOrgMode();

        // when viewing in all organisations mode, you should see all teams the user is part of
        $response = $this->get($route);
        $response->assertSee($user1->fullName);
        $response->assertSee($user2->fullName);
        $response->assertDontSee($user3->fullName);
    }

    private function prepIndexData(): array
    {
        $org = Organisation::factory()->create();
        $user1 = User::factory()->create(); // user associated via team
        $user2 = User::factory()->create(); // user associated via Libryo directly
        $user3 = User::factory()->create(); // user associated via team, but not part of org
        $org->users()->attach([$user1->id, $user2->id]);

        return [$org, $user1, $user2, $user3];
    }

    /**
     * @return void
     */
    public function testItDownloadsCorrectUserData(): void
    {
        [$org, $user1, $user2, $user3] = $this->prepIndexData();

        $this->signIn($this->mySuperUser());
        $this->withAllOrgMode();

        Excel::fake();

        $this->get(route('my.settings.users.for.organisation.export', ['organisation' => $org->id]));

        Excel::assertDownloaded("{$org->title} - Users.xlsx", function (UserExport $export) use ($org, $user3, $user2, $user1) {
            // Assert that the correct export is downloaded.
            return $export->organisation->id === $org->id
                && $export->collection()->contains(1, $user1->fname)
                && $export->collection()->contains(1, $user2->fname)
                && $export->collection()->doesntContain(1, $user3->fname)
                && $export->headings() === [
                    'Title',
                    'First Name',
                    'Last Name',
                    'Email',
                    'Lifecycle Stage',
                    'Job Description',
                ];
        });
    }
}
