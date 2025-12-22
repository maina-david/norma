<?php

namespace Tests\Feature\Storage\My\Settings;

use App\Enums\Storage\My\FolderType;
use App\Models\Customer\Organisation;
use App\Models\Geonames\Location;
use App\Models\Ontology\LegalDomain;
use App\Models\Storage\My\File;
use App\Models\Storage\My\Folder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Settings\SettingsTestCase;

class FolderStreamControllerTest extends SettingsTestCase
{
    public function testHideOrShowNormaCreatedFolders(): void
    {
        $org = Organisation::factory()->create();
        $routeName = 'my.settings.drives.setup.hide-or-show.folder';
        $folder = Folder::factory()->create(['organisation_id' => null]);
        $user = $this->assertForbiddenForNonAdmin(route($routeName, ['folder' => $folder, 'hideOrShow' => 'hide']), 'get');

        $user->organisations()->attach($org, ['is_admin' => true]);

        $this->activateAllStreams($user);

        $response = $this->assertCanAccessAfterOrgActivate(route($routeName, ['activateOrgId' => $org->id, 'folder' => $folder, 'hideOrShow' => 'hide']), 'get');
        $response->assertSee($folder->title);
        $response->assertSee('Show');

        $response = $this->assertCanAccessAfterOrgActivate(route($routeName, ['activateOrgId' => $org->id, 'folder' => $folder, 'hideOrShow' => 'show']), 'get');
        $response->assertSee($folder->title);
        $response->assertSee('Hide');
    }

    public function testIndexOrganisationFolder(): void
    {
        $org = Organisation::factory()->create();
        $routeName = 'my.settings.folder.index.for.organisation';
        $folder = Folder::factory()->create(['organisation_id' => $org->id]);
        $user = $this->assertForbiddenForNonAdmin(route($routeName, ['folder' => $folder, 'folderType' => $folder->folder_type]), 'get');

        $user->organisations()->attach($org, ['is_admin' => true]);

        $response = $this->assertCanAccessAfterOrgActivate(route($routeName, ['activateOrgId' => $org->id, 'folder' => $folder, 'folderType' => $folder->folder_type]), 'get');
        $response->assertSee('You can create a new folder using the button above');

        $response = $this->get(route($routeName, ['folder' => null, 'folderType' => $folder->folder_type]))->assertSuccessful();
        $response->assertSee($folder->title);
    }

    public function testStoreOrganisationFolder(): void
    {
        $org = Organisation::factory()->create();
        $routeName = 'my.settings.folder.store.for.organisation';
        $user = $this->assertForbiddenForNonAdmin(route($routeName), 'post');

        $user->organisations()->attach($org, ['is_admin' => true]);

        $title = 'New Folder';
        $response = $this->withActivatedOrg($org)
            ->post(route($routeName), ['title' => $title, 'folder_type' => FolderType::norma()->value]);
        $response->assertSee($title);
    }

    public function testUpdateOrganisationFolder(): void
    {
        $org = Organisation::factory()->create();
        $routeName = 'my.settings.folder.update.for.organisation';
        $parent = Folder::factory()->create(['organisation_id' => $org->id]);
        $folder = Folder::factory()->create(['organisation_id' => $org->id, 'folder_parent_id' => $parent->id]);
        $user = $this->assertForbiddenForNonAdmin(route($routeName, ['folder' => $folder]), 'put');

        $user->organisations()->attach($org, ['is_admin' => true]);

        $title = 'New Folder';
        $response = $this->withActivatedOrg($org)
            ->put(route($routeName, ['folder' => $folder, 'parentFolder' => $parent]), ['title' => $title, 'folder_type' => FolderType::norma()->value]);
        $response->assertSee($title);
        $response->assertDontSee($folder->title);
    }

    public function testDestroyOrganisationFolder(): void
    {
        $org = Organisation::factory()->create();
        $routeName = 'my.settings.folder.destroy.for.organisation';
        $folder = Folder::factory()->create(['organisation_id' => $org->id]);
        $user = $this->assertForbiddenForNonAdmin(route($routeName, ['folder' => $folder]), 'delete');

        $user->organisations()->attach($org, ['is_admin' => true]);

        $response = $this->withActivatedOrg($org)
            ->delete(route($routeName, ['folder' => $folder]));
        $response->assertDontSee($folder->title);
        $response->assertSee('You can create a new folder using the button above');
    }

    public function testIndexGlobalFolder(): void
    {
        $orgFolder = Folder::factory()->create();
        $folder = Folder::factory()->create(['folder_type' => FolderType::global()->value]);
        $child = Folder::factory()->create([
            'folder_parent_id' => $folder->id,
            'folder_type' => FolderType::global()->value,
        ]);

        $routeName = route('my.settings.folder.global.index');

        $user = $this->assertForbiddenForNonAdmin($routeName, 'get');

        $this->mySuperUser($user);

        $this->get($routeName)
            ->assertSuccessful()
            ->assertDontSee($child->title)
            ->assertDontSee($orgFolder->title)
            ->assertSee($folder->title);

        $this->get(route('my.settings.folder.global.index', ['folder' => $folder]))
            ->assertSuccessful()
            ->assertDontSee($folder->title)
            ->assertDontSee($orgFolder->title)
            ->assertSee($child->title);
    }

    public function testStoreGlobalFolder(): void
    {
        $routeName = route('my.settings.folder.global.store');
        $user = $this->assertForbiddenForNonAdmin($routeName, 'post');

        $this->mySuperUser($user);

        $title = 'New Folder';

        $this->post($routeName, ['title' => $title])
            ->assertSuccessful()
            ->assertSee($title);

        $this->assertDatabaseHas(new Folder(), [
            'title' => $title,
            'folder_parent_id' => null,
        ]);

        $folder = Folder::first();

        $child = 'Child New Folder';

        $this->post(route('my.settings.folder.global.store', ['parentFolder' => $folder->id]), ['title' => $child])
            ->assertSuccessful()
            ->assertSee($title);

        $this->assertDatabaseHas(new Folder(), [
            'title' => $child,
            'folder_parent_id' => $folder->id,
        ]);
    }

    public function testUpdateGlobalFolder(): void
    {
        $orgFolder = Folder::factory()->create();
        $folder = Folder::factory()->create(['folder_type' => FolderType::global()->value]);
        $routeName = route('my.settings.folder.global.update', ['folder' => $folder]);

        $user = $this->assertForbiddenForNonAdmin($routeName, 'put');
        $this->mySuperUser($user);

        $title = 'New Folder';
        $this->put($routeName, ['title' => $title])
            ->assertSee($title)
            ->assertDontSee($folder->title);

        $this->put(route('my.settings.folder.global.update', ['folder' => $orgFolder]), ['title' => $title])
            ->assertNotFound();
    }

    public function testDestroyGlobalFolder(): void
    {
        $orgFolder = Folder::factory()->create();
        $folder = Folder::factory()->create(['folder_type' => FolderType::global()->value]);
        $routeName = route('my.settings.folder.global.destroy', ['folder' => $folder]);

        $user = $this->assertForbiddenForNonAdmin($routeName, 'delete');
        $this->mySuperUser($user);

        $this->delete($routeName)
            ->assertDontSee($folder->title)
            ->assertSee('You can create a new folder using the button above');

        $this->delete(route('my.settings.folder.global.destroy', ['folder' => $orgFolder]))
            ->assertNotFound();
    }

    public function testCreateGlobalFile(): void
    {
        Storage::fake();
        $folder = Folder::factory()->create(['folder_type' => FolderType::global()]);
        $domain = LegalDomain::factory()->create();
        $location = Location::factory()->create();
        $routeName = route('my.settings.files.global.post', ['folder' => $folder]);

        $user = $this->assertForbiddenForNonAdmin($routeName, 'post');
        $this->mySuperUser($user);

        $file = UploadedFile::fake()->image('A Norma Image.jpg', 200, 100);

        $payload = [
            'files' => [$file],
            'legal_domains' => [$domain->id],
            'locations' => [$location->id],
        ];

        $this->post($routeName, $payload)
            ->assertSee('A Norma Image')
            ->assertSee($domain->title)
            ->assertSee($location->title);

        $payload = [
            'type' => 'global',
            'folder' => $folder->id,
            'domains' => [$domain->id],
            'jurisdictions' => [$location->id],
        ];

        $this->get(route('my.settings.drives.setup', $payload))
            ->assertSuccessful()
            ->assertSee('A Norma Image');
    }

    public function testDestroyGlobalFile(): void
    {
        $folder = Folder::factory()->create(['folder_type' => FolderType::global()]);
        $file = File::factory()->create(['folder_id' => $folder->id, 'folder_type' => FolderType::global()]);

        $routeName = route('my.settings.files.global.destroy', ['file' => $file]);

        $user = $this->assertForbiddenForNonAdmin($routeName, 'delete');
        $this->mySuperUser($user);

        $this->get(route('my.settings.folder.global.index', ['folder' => $folder]))
            ->assertSuccessful()
            ->assertSee($file->title);

        $this->delete($routeName)->assertDontSee($file->title);
    }
}
