<?php

namespace Tests\Feature\Api\Internals\My\Storage;

use App\Models\Storage\My\Folder;
use Tests\Feature\My\MyTestCase;

class FolderControllerTest extends MyTestCase
{
    public function testListingRootAndChildren(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();

        $roots = Folder::factory()
            ->count(3)
            ->create(['organisation_id' => $org->id, 'folder_parent_id' => null])
            ->sortBy('title')
            ->values();

        $children = Folder::factory()
            ->count(5)
            ->create(['organisation_id' => $org->id, 'folder_parent_id' => $roots[0]->id])
            ->sortBy('title')
            ->values();

        $this->getJson(route('api.my.storage.folders.index'))
            ->assertSuccessful()
            ->assertJsonCount(3, 'data')
            ->assertJson([
                'data' => $roots->map(fn ($item) => ['id' => $item->id])->all(),
            ]);

        $this->getJson(route('api.my.storage.folders.index', ['folder' => $roots[0]->id]))
            ->assertSuccessful()
            ->assertJsonCount(5, 'data')
            ->assertJson([
                'data' => $children->map(fn ($item) => ['id' => $item->id])->all(),
            ]);
    }
}
