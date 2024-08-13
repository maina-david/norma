<?php

namespace Tests\Unit\Services\Storage;

use App\Models\Storage\My\Folder;
use App\Services\Storage\FolderLists;
use Illuminate\Support\Str;
use Tests\TestCase;

class FolderListsTest extends TestCase
{
    public function testToCustomFolderList(): void
    {
        $folders = Folder::factory(3)->create();

        $newTitle = 'New Title - ' . Str::random();
        $settings = [
            'folder_' . $folders[0]->id => [
                'title' => $newTitle,
            ],
            'folder_' . $folders[1]->id => [
                'hidden' => true,
                'title' => $newTitle,
            ],
        ];
        $newFolders = app(FolderLists::class)->toCustomFolderList($folders, $settings);

        $this->assertSame($newTitle, $newFolders[0]->title);
        // one folder hidden...so should reduce
        $this->assertCount(2, $newFolders);
    }
}
