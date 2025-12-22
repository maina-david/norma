<?php

namespace Tests\Feature\Storage\My\Settings;

use App\Enums\Storage\My\FolderType;
use App\Models\Customer\Organisation;
use App\Models\Storage\My\Folder;
use Tests\Feature\Settings\SettingsTestCase;

class FolderControllerTest extends SettingsTestCase
{
    public function testSetup(): void
    {
        $org = Organisation::factory()->create();
        $routeName = 'my.settings.drives.setup';
        $user = $this->assertForbiddenForNonAdmin(route($routeName), 'get');

        $user->organisations()->attach($org, ['is_admin' => true]);

        $folder = Folder::factory()->create(['organisation_id' => null]);
        $folder2 = Folder::factory()->create(['organisation_id' => null, 'folder_type' => FolderType::organisation()->value]);
        $response = $this->assertCanAccessAfterOrgActivate(route($routeName, ['activateOrgId' => $org->id]), 'get');
        $response->assertSee('Organisation Drive Setup');
        $response->assertSee('Norma Stream Drive Setup');
        $response->assertSee($folder->title);
        $response->assertSee($folder2->title);
    }
}
