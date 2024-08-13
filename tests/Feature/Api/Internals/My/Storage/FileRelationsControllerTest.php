<?php

namespace Tests\Feature\Api\Internals\My\Storage;

use App\Models\Storage\My\File;
use App\Models\Tasks\Task;
use Tests\Feature\My\MyTestCase;

class FileRelationsControllerTest extends MyTestCase
{
    public function testListingFilesForRelation(): void
    {
        $this->initUserLibryoOrg();

        $task = Task::factory()->create();
        $files = File::factory()->count(3)->create();
        File::factory()->count(3)->create();
        $task->files()->attach($files->pluck('id')->all());

        $this->getJson(route('api.my.storage.files.related.index', ['relation' => 'task', 'related' => $task->id]))
            ->assertSuccessful()
            ->assertJsonCount(3, 'data')
            ->assertJson([
                'data' => $files->map(fn ($file) => ['id' => $file->id])->all(),
            ]);
    }
}
