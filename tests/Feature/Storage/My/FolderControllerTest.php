<?php

namespace Tests\Feature\Storage\My;

use App\Enums\Storage\My\FolderType;
use App\Models\Customer\Organisation;
use App\Models\Storage\My\File;
use App\Models\Storage\My\Folder;
use App\Services\Customer\ActiveLibryosManager;
use App\Services\Customer\ActiveOrganisationManager;
use Tests\Feature\My\MyTestCase;

class FolderControllerTest extends MyTestCase
{
    public function testRoot(): void
    {
        $user = $this->mySuperUser();
        $org = Organisation::factory()->create();
        $user->organisations()->attach($org);
        $this->signIn($user);
        app(ActiveLibryosManager::class)->activateAll($user);
        $route = route('my.drives.root');
        $response = $this->get($route);
        $response->assertSee('Shared Files');
    }

    public function testShowRootFolder(): void
    {
        $user = $this->mySuperUser();
        $org = Organisation::factory()->create();
        $user->organisations()->attach($org);
        $this->signIn($user);
        $this->activateAllStreams($user);
        app(ActiveOrganisationManager::class)->activate($org->id);
        $route = route('my.drives.folders.show.root', ['type' => 'organisation']);
        $response = $this->get($route);
        $response->assertSee('Organisation\'s Drive');
    }

    public function testShowRootFolderThisStream(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $route = route('my.drives.folders.show.root', ['type' => 'libryo']);
        $response = $this->get($route);
        $response->assertSee('This Libryo Stream\'s Drive');
        $response->assertSee($libryo->title);
    }

    public function testShowRootFolderGlobal(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $route = route('my.drives.folders.show.root', ['type' => 'global']);
        $response = $this->get($route);
        $response->assertSee('Shared Files');
    }

    public function testShowGlobalFolder(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $folder = Folder::factory()->create([
            'folder_type' => FolderType::global()->value,
        ]);
        $file = File::factory()->for($folder)->for($org)->create([
            'folder_type' => FolderType::global()->value,
        ]);
        $route = route('my.drives.folders.show', ['folder' => $folder->id]);
        $response = $this->get($route);
        $response->assertSee($folder->title);
        $response->assertSee($file->title);
    }

    public function testShowOrganisationFolder(): void
    {
        $user = $this->mySuperUser();
        $org = Organisation::factory()->create();
        $user->organisations()->attach($org);
        $this->signIn($user);
        $this->activateAllStreams($user);
        app(ActiveOrganisationManager::class)->activate($org->id);
        $folder = Folder::factory()->create([
            'folder_type' => FolderType::organisation()->value,
        ]);
        $file = File::factory()->for($folder)->for($org)->create([
            'folder_type' => FolderType::organisation()->value,
        ]);
        $route = route('my.drives.folders.show', ['folder' => $folder->id]);
        $response = $this->get($route);
        $response->assertSee($folder->title);
        $response->assertSee($file->title);
    }

    public function testShowLibryoFolder(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $folder = Folder::factory()->create([
            'folder_type' => FolderType::libryo()->value,
        ]);
        $file = File::factory()->for($folder)->for($org)->create([
            'place_id' => $libryo->id,
        ]);
        $route = route('my.drives.folders.show', ['folder' => $folder->id]);
        $response = $this->get($route);
        $response->assertSee($folder->title);
        $response->assertSee($file->title);

        $childFolder = Folder::factory()->create([
            'folder_type' => FolderType::libryo()->value,
            'folder_parent_id' => $folder->id,
        ]);
        $route = route('my.drives.folders.show', ['folder' => $childFolder->id]);
        $response = $this->get($route);
        $response->assertSee($childFolder->title);
    }
}
