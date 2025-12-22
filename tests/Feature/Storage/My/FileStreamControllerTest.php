<?php

namespace Tests\Feature\Storage\My;

use App\Enums\Storage\My\FolderType;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Comments\Comment;
use App\Models\Storage\My\File;
use App\Models\Storage\My\Folder;
use App\Models\Tasks\Task;
use Tests\Feature\My\MyTestCase;

class FileStreamControllerTest extends MyTestCase
{
    public function testForAssessmentItemResponse(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();

        $folder = Folder::factory()->create([
            'folder_type' => FolderType::norma()->value,
        ]);
        $files = File::factory(2)->for($folder)->create([
            'folder_type' => FolderType::norma()->value,
            'place_id' => $norma->id,
        ]);

        $aiResponse = AssessmentItemResponse::factory()->for($norma)->create(['last_answered_by' => $user->id]);

        $aiResponse->files()->attach($files);
        $routeName = 'my.drives.files.for.assessment-item-response.index';
        $response = $this->get(route($routeName, ['response' => $aiResponse]))->assertSuccessful();
        $response->assertSee($files[0]->title);
        $response->assertSee($files[1]->title);
    }

    public function testForTask(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();

        $folder = Folder::factory()->create([
            'folder_type' => FolderType::norma()->value,
        ]);
        $files = File::factory(2)->for($folder)->create([
            'folder_type' => FolderType::norma()->value,
            'place_id' => $norma->id,
        ]);

        $task = Task::factory()->for($norma)->create();

        $task->files()->attach($files);
        $routeName = 'my.drives.files.for.task.index';
        $response = $this->get(route($routeName, ['task' => $task]))->assertSuccessful();
        $response->assertSee($files[0]->title);
        $response->assertSee($files[1]->title);
    }

    public function testForComment(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();

        $folder = Folder::factory()->create([
            'folder_type' => FolderType::norma()->value,
        ]);
        $files = File::factory(2)->for($folder)->create([
            'folder_type' => FolderType::norma()->value,
            'place_id' => $norma->id,
        ]);

        $file = File::factory()->for($folder)->create([
            'folder_type' => FolderType::norma()->value,
            'place_id' => $norma->id,
        ]);

        $comment = Comment::factory()->create([
            'place_id' => $norma->id,
            'commentable_type' => 'file',
            'commentable_id' => $file->id,
            'author_id' => $user->id,
        ]);

        $comment->files()->attach($files);
        $routeName = 'my.drives.files.for.comment.index';
        $response = $this->get(route($routeName, ['comment' => $comment]))->assertSuccessful();
        $response->assertSee($files[0]->title);
        $response->assertSee($files[1]->title);
    }
}
