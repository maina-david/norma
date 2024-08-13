<?php

namespace Tests\Feature\Storage\Collaborate;

use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Storage\Collaborate\Attachment;
use App\Models\Workflows\Pivots\TaskAttachment;
use App\Models\Workflows\Task;
use App\Models\Workflows\TaskType;
use HotwiredLaravel\TurboLaravel\Testing\AssertableTurboStream;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TaskAttachmentControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testWorksWithAttachments(): void
    {
        $type = TaskType::factory()->create();
        $role = Role::factory()->collaborate()->create(['permissions' => [$type->permission(false), 'storage.attachment.viewAny']]);
        $user = User::factory()->hasAttached($role)->create();
        $this->signIn($user);

        $this->applyTaskTypesPermissions();

        $task = Task::factory()->create(['task_type_id' => $type->id, 'user_id' => $user->id]);

        Storage::fake();
        $file = UploadedFile::fake()->image('upload.jpg');
        $index = route('collaborate.task.attachments.index', ['task' => $task->id]);

        $this->assertDatabaseMissing(Attachment::class, ['name' => 'upload.jpg', 'author_id' => $user->id]);
        $this->assertDatabaseMissing(TaskAttachment::class, ['task_id' => $task->id]);

        $this->post(route('collaborate.task.attachments.store', ['task' => $task->id]), ['file' => $file])
            ->assertRedirect($index);

        $this->assertDatabaseHas(Attachment::class, ['name' => 'upload.jpg']);
        $this->assertDatabaseHas(TaskAttachment::class, ['task_id' => $task->id]);

        $this->get($index, ['turbo-frame' => 'frame'])
            ->assertSuccessful()
            ->assertTurboStream(function (AssertableTurboStream $streams) {
                return $streams->has(1)
                    ->hasTurboStream(function ($stream) {
                        return $stream->where('action', 'update')->see('upload.jpg')->see('trash-alt');
                    });
            });

        $attachment = Attachment::where(['name' => 'upload.jpg'])->first();

        $this->get(route('collaborate.task.attachments.show', ['task' => $task->id, 'attachment' => $attachment]))
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'image/jpeg');

        $this->delete(route('collaborate.task.attachments.destroy', ['task' => $task->id, 'attachment' => $attachment]))
            ->assertRedirect($index);

        $this->get($index, ['turbo-frame' => 'frame'])
            ->assertSuccessful()
            ->assertDontSee('upload.jpg');

        $super = $this->collaborateSuperUser();

        $file = UploadedFile::fake()->image('upload.jpg');
    }
}
