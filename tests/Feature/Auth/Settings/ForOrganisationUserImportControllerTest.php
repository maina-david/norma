<?php

namespace Tests\Feature\Auth\Settings;

use App\Exports\Customer\Settings\UserImportTemplateExport;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Auth\UserRole;
use App\Models\Customer\Organisation;
use App\Models\Customer\Pivots\OrganisationUser;
use App\Models\Customer\Pivots\TeamUser;
use App\Models\Customer\Team;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Feature\Settings\SettingsTestCase;

class ForOrganisationUserImportControllerTest extends SettingsTestCase
{
    /**
     * @return void
     */
    public function testImportForbiddenForNonSuperAdmin(): void
    {
        $org = Organisation::factory()->create();

        $route = route('my.settings.users.for.organisation.import.create');

        $user = $this->assertForbiddenForNonAdmin($route, 'get');

        $user->organisations()->attach($org);

        $this->withExceptionHandling()->get($route)->assertForbidden();

        $user->organisations()->updateExistingPivot($org, ['is_admin' => true]);

        $this->withExceptionHandling()->get($route)->assertSuccessful();
    }

    /**
     * @return void
     */
    public function testImportNotPossibleWhenInAllOrgsMode(): void
    {
        $org = Organisation::factory()->create();
        $user = $this->activateAllOrg();
        $user->organisations()->attach($org->id, ['is_admin' => true]);

        $this->get(route('my.settings.users.index'))
            ->assertSuccessful()
            ->assertDontSee('Import');

        $this->withExceptionHandling()
            ->get(route('my.settings.users.for.organisation.import.create'))
            ->assertNotFound();

        $this->assertCanAccessAfterOrgActivate(route('my.settings.users.index', ['activateOrgId' => $org->id]), 'get')
            ->assertSuccessful()
            ->assertSee('Import');

        $this->get(route('my.settings.users.for.organisation.import.create'))
            ->assertSuccessful()
            ->assertSee('Import');
    }

    /**
     * @return void
     */
    public function testItDownloadsATemplate(): void
    {
        Excel::fake();

        $org = Organisation::factory()->create();
        $user = $this->mySuperUser();
        $user->organisations()->attach($org->id, ['is_admin' => true]);

        $this->signIn($user);

        $this->get(route('my.settings.users.for.organisation.import.template'))->assertSuccessful();

        Excel::assertDownloaded('Import Users Template.xlsx', function (UserImportTemplateExport $export) {
            $headings = $export->headings();

            return $headings[0] === 'First Name'
                && $headings[1] === 'Last Name'
                && $headings[2] === 'Email'
                && $headings[3] === 'Mobile Country Code'
                && $headings[4] === 'Mobile Phone'
                && $export->collection()->isEmpty();
        });
    }

    /**
     * @return void
     */
    public function testItImportsATemplate(): void
    {
        $role = Role::factory()->my()->create(['title' => 'My Users']);
        $team = Team::factory()->create();
        $org = Organisation::factory()->create();
        $user = $this->mySuperUser();
        $user->organisations()->attach($org->id, ['is_admin' => true]);

        $this->signIn($user);

        $this->assertCanAccessAfterOrgActivate(route('my.settings.users.index', ['activateOrgId' => $org->id]), 'get')
            ->assertSuccessful()
            ->assertSee('Import')
            ->assertDontSee('Zebra')
            ->assertDontSee('Corn');

        $this->get(route('my.settings.users.for.organisation.import.create'))
            ->assertSuccessful()
            ->assertSee('Teams')
            ->assertSee('Upload Files')
            ->assertSee($org->title . ' - Import Users');

        $content = file_get_contents(__DIR__ . '/../../../files/user_imports.xlsx');

        $file = UploadedFile::fake()->createWithContent('upload.xlsx', $content);

        $this->assertDatabaseMissing(User::class, ['email' => 'zebra@corn.test']);

        $this->followingRedirects()
            ->post(route('my.settings.users.for.organisation.import.store'), ['team_id' => [$team->id], 'users' => $file])
            ->assertSessionHasNoErrors()
            ->assertSee('Zebra')
            ->assertSee('Corn');

        $this->assertDatabaseHas(User::class, ['email' => 'zebra@corn.test']);

        $user = User::where(['email' => 'zebra@corn.test'])->firstOrFail();

        $this->assertDatabaseHas(UserRole::class, ['user_id' => $user->id, 'role_id' => $role->id]);

        $imported = User::where('email', 'zebra@corn.test')->firstOrFail();

        $this->assertDatabaseHas(TeamUser::class, ['team_id' => $team->id, 'user_id' => $imported->id]);
        $this->assertDatabaseHas(OrganisationUser::class, ['organisation_id' => $org->id, 'user_id' => $imported->id]);
    }
}
