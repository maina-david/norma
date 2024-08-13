<?php

namespace Tests\Feature\Api\Internals\My\Storage;

use App\Models\Storage\My\Folder;
use App\Models\Tasks\Task;
use Illuminate\Http\UploadedFile;
use Tests\Feature\My\MyTestCase;

class FileControllerTest extends MyTestCase
{
    public function testStoringFiles(): void
    {
        [$user, $libryo] = $this->initUserLibryoOrg();
        $task = Task::factory()->create();
        $folder = Folder::factory()->create();

        $file = UploadedFile::fake()->image('file.jpg');

        $payload = [
            'file' => $file,
            'relation' => 'task',
            'related_id' => $task->id,
            'folder_id' => $folder->id,
            'target_libryo_id' => $libryo->id,
        ];

        $this->postJson(route('api.my.storage.files.store'), $payload)
            ->assertSuccessful()
            ->assertJson([
                'data' => [
                    'extension' => $file->extension(),
                ],
            ]);
    }
}
