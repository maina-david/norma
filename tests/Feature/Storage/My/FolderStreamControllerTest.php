<?php

namespace Tests\Feature\Storage\My;

use App\Enums\Storage\My\FolderType;
use App\Models\Customer\Organisation;
use App\Models\Storage\My\Folder;
use App\Services\Customer\ActiveLibryosManager;
use App\Services\Customer\ActiveOrganisationManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Tests\Feature\My\MyTestCase;

class FolderStreamControllerTest extends MyTestCase
{
    public function testTree(): void
    {
        $user = $this->mySuperUser();
        $org = Organisation::factory()->create();
        $user->organisations()->attach($org);
        $this->signIn($user);
        $this->activateAllStreams($user);
        app(ActiveOrganisationManager::class)->activate($org->id);

        $folders = Folder::factory(3)->for($org)->create([
            'folder_type' => FolderType::organisation()->value,
        ]);

        $this->testTreeView($folders);

        $folder = Folder::factory()->for($org)->create(['folder_type' => FolderType::organisation()->value]);
        $folders = Folder::factory(3)->for($org)->create([
            'folder_type' => FolderType::organisation()->value,
            'folder_parent_id' => $folder->id,
        ]);
        $this->testTreeView($folders, $folder);
    }

    public function testTreeForSingleStream(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        app(ActiveLibryosManager::class)->activate($user, $libryo);

        $folders = Folder::factory(3)->for($org)->create([
            'folder_type' => FolderType::libryo()->value,
        ]);

        $this->testTreeView($folders);
    }

    private function testTreeView(Collection $folders, ?Folder $parent = null): void
    {
        $route = route('my.drives.folders.tree', ['uniqueId' => Str::random(), 'folder' => $parent]);
        $response = $this->get($route);
        $response->assertSee($folders[0]->title);
        $response->assertSee($folders[1]->title);
    }
}
